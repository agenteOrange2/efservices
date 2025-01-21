<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Carrier;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\DriverConfirmationMail;

class DriverRegistrationController extends Controller
{
    public function showRegistrationForm(Request $request, Carrier $carrier)
    {
        $token = $request->query('token');
        
        Log::info('Starting driver registration validation', [
            'carrier_slug' => $carrier->slug,
            'carrier_status' => $carrier->status,
            'provided_token' => $token,
            'carrier_token' => $carrier->referrer_token,
            'current_drivers' => $carrier->userDrivers()->count(),
            'max_drivers' => $carrier->membership->max_drivers ?? 1
        ]);
    
        // 1. Validar el token de referencia primero
        if ($carrier->referrer_token !== $token) {
            Log::warning('Invalid referral token', [
                'provided_token' => $token,
                'carrier_token' => $carrier->referrer_token
            ]);
            return redirect()->route('driver.register.error')
                ->with('error', 'Invalid registration link.');
        }
    
        // 2. Validar el estado del carrier
        if ($carrier->status === Carrier::STATUS_PENDING) {
            return redirect()->route('driver.carrier.status')
                ->with('error', 'This carrier is pending approval. Please try again later.');
        }
    
        if ($carrier->status === Carrier::STATUS_INACTIVE) {
            return redirect()->route('driver.carrier.status')
                ->with('error', 'This carrier is currently inactive. Please contact the administrator.');
        }
    
        if ($carrier->status !== Carrier::STATUS_ACTIVE) {
            return redirect()->route('driver.carrier.status')
                ->with('error', 'This carrier is not currently accepting new registrations.');
        }
    
        // 3. Verificar límite de drivers
        $currentDriversCount = $carrier->userDrivers()->count();
        $maxDrivers = $carrier->membership->max_drivers ?? 1;
    
        Log::info('Checking driver quota', [
            'current_count' => $currentDriversCount,
            'max_allowed' => $maxDrivers
        ]);
    
        if ($currentDriversCount >= $maxDrivers) {
            return redirect()->route('driver.quota-exceeded')
                ->with('error', 'This carrier has reached their maximum driver quota.');
        }
    
        // 4. Si todo está bien, mostrar el formulario
        $registerUrl = route('driver.register.submit', [
            'carrier' => $carrier->slug,
            'token' => $token
        ]);
    
        Log::info('Registration form ready to display', [
            'register_url' => $registerUrl
        ]);
    
        return view('auth.user_driver.register', compact('carrier', 'registerUrl'));
    }

    public function register(Request $request, $carrierSlug)
    {
        // Validar el carrier y el token nuevamente
        $carrier = Carrier::where('slug', $carrierSlug)
            ->where('referrer_token', $request->token)
            ->where('status', Carrier::STATUS_ACTIVE)
            ->firstOrFail();

        // Validar el límite de drivers nuevamente
        $currentDriversCount = $carrier->userDrivers()->count();
        $maxDrivers = $carrier->membership->max_drivers ?? 1;

        if ($currentDriversCount >= $maxDrivers) {
            return redirect()->route('driver.quota-exceeded');
        }

        // Validar los datos del formulario
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:15',
            'license_number' => 'required|string|max:255',
            'birth_date' => 'required|date|before:today', // Fecha de nacimiento
            'years_experience' => 'required|integer|min:0|max:50', // Años de experiencia
            'address' => 'required|string|max:255', // Dirección
        ]);

        // Crear el usuario con rol de driver
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => UserDriverDetail::STATUS_PENDING,
        ]);
        
        $user->assignRole('driver');

        // Crear los detalles del driver
        $driverDetails = $user->driverDetails()->create([
            'carrier_id' => $carrier->id,
            'license_number' => $validated['license_number'],
            'phone' => $validated['phone'],
            'birth_date' => $validated['birth_date'],
            'years_experience' => $validated['years_experience'],
            'address' => $validated['address'],
            'status' => UserDriverDetail::STATUS_PENDING,
            'confirmation_token' => Str::random(32),
        ]);

        // Enviar correo de confirmación
        //Mail::to($user->email)->send(new DriverConfirmationMail($driverDetails));

        // Redirigir a la página de éxito
        return redirect()->route('driver.registration.success')
            ->with('status', 'Please check your email to confirm your registration.');
    }

    public function confirmEmail($token)
    {
        $driverDetails = UserDriverDetail::where('confirmation_token', $token)->firstOrFail();

        $driverDetails->update([
            'confirmation_token' => null,
            'email_verified_at' => now(),
        ]);

        // Autenticar al usuario
        Auth::login($driverDetails->user);

        // Redirigir al primer paso del registro completo
        return redirect()->route('driver.registration.step1');
    }

    public function showStep1()
    {
        return view('auth.driver.steps.step1');
    }

    public function processStep1(Request $request)
    {
        $validated = $request->validate([
            'birth_date' => 'required|date',
            'years_experience' => 'required|integer|min:0',
            'address' => 'required|string|max:255',
        ]);

        $driver = auth()->user()->driverDetails;
        $driver->update($validated);

        // Guardar en sesión que completó el paso 1
        session(['driver_registration_step' => 1]);

        return redirect()->route('driver.registration.step2');
    }

    public function showStep2()
    {
        // Verificar que haya completado el paso 1
        if (!session('driver_registration_step')) {
            return redirect()->route('driver.registration.step1');
        }

        return view('auth.driver.steps.step2');
    }

    public function processStep2(Request $request)
    {
        $validated = $request->validate([
            'profile_photo' => 'nullable|image|max:2048',
            // Aquí irían más campos cuando los tengamos
        ]);

        $driver = auth()->user()->driverDetails;

        if ($request->hasFile('profile_photo')) {
            $driver->user->addMediaFromRequest('profile_photo')
                ->usingFileName(Str::slug($driver->user->name) . '.webp')
                ->toMediaCollection('profile_photo_driver');
        }

        // Limpiar la sesión de pasos
        session()->forget('driver_registration_step');

        // Marcar el registro como completado
        $driver->update(['registration_completed' => true]);

        return redirect()->route('driver.dashboard')
            ->with('status', 'Registration completed successfully. Please wait for admin approval.');
    }
}
