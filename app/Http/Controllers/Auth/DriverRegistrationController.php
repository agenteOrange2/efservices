<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Carrier;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Mail\DriverConfirmationMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Admin\Driver\NewDriverRegistrationNotification;

class DriverRegistrationController extends Controller
{
    /**
     * Muestra el formulario de registro para drivers que llegan con referencia
     */
    public function showRegistrationForm(Request $request, Carrier $carrier)
    {
        $token = $request->route('token') ?? $request->query('token');
        $isIndependent = empty($token);
        
        // Solo validamos el token si no es registro independiente
        if (!$isIndependent && !$this->validateTokenAndCarrier($carrier, $token)) {
            return redirect()->route('driver.register.error');
        }
    
        return view('auth.user_driver.register', [
            'carrier' => $carrier,
            'isIndependent' => $isIndependent,
            'token' => $token
        ]);
    }

    /**
     * Muestra el formulario de selección de carrier para drivers independientes
     */
    public function showIndependentCarrierSelection()
    {
        try {
            // Log para depuración
            Log::info('showIndependentCarrierSelection called');
            
            // Obtener carriers activos de manera simple
            $carriers = Carrier::where('status', Carrier::STATUS_ACTIVE)->get();
            
            return view('auth.user_driver.select_carrier_registration', [
                'carriers' => $carriers,
                'isRegistration' => true
            ]);
        } catch (\Exception $e) {
            Log::error('Error en showIndependentCarrierSelection: ' . $e->getMessage());
            return redirect()->route('driver.register.error')
                ->with('error', 'Error loading carriers. Please try again later.');
        }
    }
    

    /**
     * Muestra el formulario de registro para drivers independientes (sin referencia)
     * pero ya con un carrier seleccionado
     */
    public function showIndependentRegistrationForm($carrier_slug)
    {
        try {
            // Buscar el carrier por slug
            $carrier = Carrier::where('slug', $carrier_slug)->firstOrFail();
            
            // Renderizar vista de registro
            return view('auth.user_driver.register', [
                'isIndependent' => true,
                'carrier' => $carrier,
                'token' => null
            ]);
        } catch (\Exception $e) {
            Log::error('Error en showIndependentRegistrationForm', [
                'carrier_slug' => $carrier_slug,
                'error_message' => $e->getMessage()
            ]);
            
            return redirect()->route('driver.register.error')
                ->with('error', 'No se pudo encontrar el carrier seleccionado.');
        }
    }

    /**
     * Procesa el registro de conductores que llegan con referencia
     */
    public function register(Request $request, $carrierSlug)
    {
        $carrier = Carrier::where('slug', $carrierSlug)
            ->where('referrer_token', $request->token)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'middle_name' => 'nullable|string',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'password' => 'required|min:8|confirmed',
            'license_number' => 'required|string',
            'phone' => 'required|string',
            'terms_accepted' => 'required|accepted'
        ]);

        // Crear el usuario y asignar rol
        $user = $this->createUser($validated);

        // Crear driver details con carrier asociado
        $driverDetails = $this->createDriverDetails($user, $validated, $carrier->id);

        // Enviar email de confirmación
        //Mail::to($user->email)->send(new DriverConfirmationMail($driverDetails));

        return redirect()->route('driver.registration.success')->with([
            'message' => 'Registration successful! Please check your email to confirm your account.',
            'carrier_name' => $carrier->name
        ]);
    }

    /**
     * Procesa el registro de conductores independientes (ahora con carrier seleccionado)
     */
    public function registerIndependent(Request $request)
    {
        Log::info('registerIndependent llamado', [
            'request_data' => $request->all()
        ]);
        
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'middle_name' => 'nullable|string',
                'last_name' => 'required|string|max:255',
                'date_of_birth' => 'required|date',
                'password' => 'required|min:8|confirmed',
                'license_number' => 'required|string',
                'phone' => 'required|string',
                'terms_accepted' => 'required|accepted',
                'carrier_slug' => 'required|exists:carriers,slug'  // Cambiar a carrier_slug
            ]);
    
            // Buscar carrier por slug
            $carrier = Carrier::where('slug', $validated['carrier_slug'])->firstOrFail();
    
            // Crear el usuario y asignar rol
            $user = $this->createUser($validated);
    
            // Crear driver details con carrier asociado
            $driverDetails = $this->createDriverDetails($user, $validated, $carrier->id);
    
            return redirect()->route('driver.registration.success')->with([
                'message' => 'Registration successful! Please check your email to confirm your account.',
                'carrier_name' => $carrier->name
            ]);
        } catch (\Exception $e) {
            Log::error('Error en registerIndependent: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error processing registration. Please try again.']);
        }
    }
    /**
     * Confirma el correo electrónico del conductor
     */
    public function confirmEmail($token)
    {
        $driver = UserDriverDetail::where('confirmation_token', $token)->firstOrFail();

        $driver->update([
            'confirmation_token' => null,
            'email_verified_at' => now()
        ]);

        // Si es un registro independiente (sin carrier asignado)
        if (!$driver->carrier_id) {
            Auth::login($driver->user);
            return redirect()->route('driver.select_carrier')
                ->with('success', 'Email confirmed! Please select a carrier to work with.');
        }

        return redirect()->route('login')
            ->with('success', 'Email confirmed. Please log in to complete your registration.');
    }

    /**
     * Muestra la página para seleccionar un carrier (para registros independientes)
     */
    public function showSelectCarrier()
    {
        if (!Auth::check() || !Auth::user()->hasRole('driver')) {
            return redirect()->route('login');
        }

        $driver = Auth::user()->driverDetails;

        // Si ya tiene un carrier asignado, redirigir
        if ($driver && $driver->carrier_id) {
            return redirect()->route('driver.dashboard');
        }

        $carriers = Carrier::where('status', Carrier::STATUS_ACTIVE)->get();

        return view('auth.user_driver.select_carrier', compact('carriers'));
    }

    /**
     * Procesa la selección de carrier
     */
    public function selectCarrier(Request $request)
    {
        $request->validate([
            'carrier_id' => 'required|exists:carriers,id'
        ]);

        $user = Auth::user();
        $driver = $user->driverDetails;

        if (!$driver) {
            return redirect()->route('driver.register.error');
        }

        $carrier = Carrier::findOrFail($request->carrier_id);

        // Verificar si el carrier puede aceptar más conductores
        if ($carrier->userDrivers()->count() >= ($carrier->membership->max_drivers ?? 1)) {
            return back()->with('error', 'This carrier has reached its maximum number of drivers.');
        }

        // Asignar carrier al driver
        $driver->update([
            'carrier_id' => $carrier->id,
            'status' => UserDriverDetail::STATUS_PENDING
        ]);

        // Opcionalmente, notificar al carrier
        //$carrier->userAdmin->notify(new NewDriverRegistrationNotification($driver));

        return redirect()->route('driver.dashboard')
            ->with('success', "You have successfully joined {$carrier->name}. Your application is now pending approval.");
    }

    /**
     * Valida si un token y carrier son válidos para el registro
     */
    private function validateTokenAndCarrier(Carrier $carrier, $token)
    {
        if (
            $carrier->referrer_token !== $token ||
            $carrier->status !== Carrier::STATUS_ACTIVE ||
            $carrier->userDrivers()->count() >= ($carrier->membership->max_drivers ?? 1)
        ) {
            return false;
        }
        return true;
    }

    /**
     * Crea un nuevo usuario
     */
    private function createUser($data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);

        $user->assignRole('driver');

        return $user;
    }

    /**
     * Crea los detalles del conductor
     */
    private function createDriverDetails($user, $data, $carrierId = null)
    {
        return $user->driverDetails()->create([
            'carrier_id' => $carrierId,
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'date_of_birth' => $data['date_of_birth'],
            'license_number' => $data['license_number'] ?? null,
            'phone' => $data['phone'],
            'status' => UserDriverDetail::STATUS_PENDING,
            'confirmation_token' => Str::random(32),
            'current_step' => 1
        ]);
    }
}
