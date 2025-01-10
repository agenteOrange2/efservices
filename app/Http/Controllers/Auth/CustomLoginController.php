<?php

namespace App\Http\Controllers\Auth;

use App\Models\Carrier;
use App\Helpers\Constants;
use App\Models\Membership;
use App\Traits\GeneratesBaseDocuments;
use App\Models\UserCarrier;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\CarrierConfirmationMail;


class CustomLoginController
{
    use GeneratesBaseDocuments;

    public function showLoginForm(Request $request)
    {
        if ($request->is('user-carrier/*')) {
            return view('auth.user_carrier.login'); // Vista para user_carrier
        }

        if ($request->is('user-driver/*')) {
            return view('auth.user_driver.login'); // Vista para user_driver
        }

        return view('auth.login'); // Vista genérica
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        Log::info('Login attempt:', ['email' => $credentials['email']]);
    
        $userCarrier = UserCarrier::where('email', $credentials['email'])->first();
    
        if (!$userCarrier) {
            Log::info('User not found:', ['email' => $credentials['email']]);
            return back()->withErrors(['email' => 'The provided credentials do not match our records.']);
        }
    
        Log::info('User found:', ['id' => $userCarrier->id, 'carrier_id' => $userCarrier->carrier_id]);
    
        if (!Hash::check($credentials['password'], $userCarrier->password)) {
            Log::info('Password check failed:', ['id' => $userCarrier->id]);
            return back()->withErrors(['email' => 'The provided credentials do not match our records.']);
        }
    
        Log::info('Password check passed:', ['id' => $userCarrier->id]);
    
        Auth::guard('user_carrier')->login($userCarrier, $request->boolean('remember'));
        Log::info('Auth guard user_carrier login successful', [
            'user_id' => $userCarrier->id,
            'guard' => 'user_carrier'
        ]);
    
        $request->session()->regenerate();
        Log::info('Session regenerated after login for user', ['user_id' => $userCarrier->id]);
    
        if (!$userCarrier->carrier_id) {
            Log::info('User has no carrier_id. Redirecting to complete registration.', ['id' => $userCarrier->id]);
            return redirect()->route('user_carrier.complete_registration')
                ->with('status', 'Please complete your carrier registration.');
        }
    
        Log::info('User has carrier_id. Redirecting to admin dashboard.', ['id' => $userCarrier->id]);
        return redirect()->intended('/admin');
    }
    



    public function showRegisterForm(Request $request)
    {
        if ($request->is('user-carrier/*')) {
            return view('auth.user_carrier.register'); // Vista para user_carrier
        }

        if ($request->is('user-driver/*')) {
            return view('auth.user_driver.register'); // Vista para user_driver
        }

        abort(404); // Mostrar error si no corresponde a ninguna ruta válida
    }

    public function register(Request $request)
    {
        if ($request->routeIs('user_carrier.*')) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:user_carriers,email',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'required|string|max:15',
                'job_position' => 'required|string|max:255',
            ]);

            $validated['password'] = Hash::make($validated['password']);
            $validated['status'] = UserCarrier::STATUS_PENDING;

            // Crear el usuario carrier
            $userCarrier = UserCarrier::create($validated);

            // Asignar el rol automáticamente
            $userCarrier->assignRole('user_carrier');
            Log::info('Rol asignado al desde el formulario de registro UserCarrier.', ['user_carrier_id' => $userCarrier->id, 'role' => 'user_carrier']);

            // Generar un token de confirmación
            $token = Str::random(32);
            $userCarrier->update(['confirmation_token' => $token]);

            // Enviar correo de confirmación
            Mail::to($userCarrier->email)->send(new CarrierConfirmationMail($userCarrier));

            return redirect()->route('user_carrier.login')->with('status', 'Registration successful. Please check your email to confirm.');
        }
    }



    public function confirmEmail($token)
    {
        // Busca al usuario usando el token de confirmación
        $userCarrier = UserCarrier::where('confirmation_token', $token)->first();
    
        if (!$userCarrier) {
            // Si no encuentra el token o es inválido
            return redirect()->route('user_carrier.login')->withErrors([
                'email' => 'Invalid or expired confirmation token.',
            ]);
        }
    
        // Actualiza el estado del correo electrónico y elimina el token para que no pueda usarse de nuevo
        $userCarrier->update([
            'email_verified_at' => now(),
            'confirmation_token' => null,
        ]);
    
        // Autenticar al usuario
        Auth::guard('user_carrier')->login($userCarrier);
    
        // Verificar si el Carrier ya está registrado
        if (!$userCarrier->carrier_id) {
            // Si no está registrado, redirigir a completar el registro
            return redirect()->route('user_carrier.complete_registration')
                ->with('status', 'Please complete your carrier registration.');
        }
    
        // Si ya está registrado, redirigir al dashboard
        return redirect()->route('admin.dashboard')
            ->with('status', 'Your email has been confirmed. Welcome to the admin dashboard!');
    }
    
    


    public function showCompleteRegistrationForm(Request $request)
    {
        // Obtener los estados desde el helper
        $usStates = Constants::usStates();

        // Obtener otras configuraciones necesarias, como membresías (si aplica)
        $memberships = Membership::where('status', 1)->select('id', 'name')->get();

        return view('auth.user_carrier.complete_registration', compact('usStates', 'memberships'));
    }

    public function completeRegistration(Request $request)
    {
        Log::info('Inicio del proceso de registro del Carrier.', [
            'user_id' => Auth::id(),
            'request_data' => $request->all(),
        ]);

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'state' => 'required|string|max:255',
                'zipcode' => 'required|string|max:10',
                'ein_number' => 'required|string|max:255',
                'dot_number' => 'required|string|max:255',
                'mc_number' => 'nullable|string|max:255',
                'state_dot' => 'nullable|string|max:255',
                'ifta_account' => 'nullable|string|max:255',
                'id_plan' => 'required|exists:memberships,id',
            ]);

            Log::info('Validación completada con éxito.', ['validated_data' => $validated]);

            // Usuario actual autenticado
            $userCarrier = Auth::guard('user_carrier')->user();

            Log::info('Usuario autenticado.', ['user' => $userCarrier->toArray()]);

            // Crear y asociar el Carrier
            $carrier = $userCarrier->carrier()->create(array_merge($validated, [
                'slug' => Str::slug($validated['name']),
                'referrer_token' => Str::random(16),
                'status' => Carrier::STATUS_PENDING, // Asignar estado pendiente
            ]));

            Log::info('Carrier creado con éxito.', ['carrier' => $carrier->toArray()]);

            // Asignar documentos base
            $this->generateBaseDocuments($carrier);
            Log::info('Documentos base generados para el Carrier.', ['carrier_id' => $carrier->id]);


            Log::info('Usuario Carrier actualizado con éxito.', [
                'user_carrier' => $userCarrier->toArray(),
            ]);

            $userCarrier->assignRole('user_carrier');
            Log::info('Rol asignado al UserCarrier.', ['user_carrier_id' => $userCarrier->id, 'role' => 'user_carrier']);

            // Asociar el Carrier al usuario y actualizar su estado
            $userCarrier->update([
                'carrier_id' => $carrier->id,
                'status' => UserCarrier::STATUS_PENDING, // Cambiar el estado del usuario
            ]);

            Log::info('Usuario Carrier actualizado con éxito.', [
                'user_carrier' => $userCarrier->toArray(),
            ]);

            // Redirigir a una vista de confirmación
            return redirect()->route('user_carrier.confirmation')
                ->with('status', 'Your registration has been submitted and is under review.');
        } catch (\Exception $e) {
            Log::error('Error en el proceso de registro del Carrier.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'An error occurred during registration. Please try again later.'])
                ->withInput();
        }
    }
}
