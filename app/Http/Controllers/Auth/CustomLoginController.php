<?php

namespace App\Http\Controllers\Auth;

use App\Models\Carrier;
use App\Models\UserDriver;
use App\Models\UserCarrier;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\CarrierConfirmationMail;


class CustomLoginController
{
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
        $guard = 'user_carrier'; // Guard específico para user_carrier
        Auth::shouldUse($guard);
    
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        $userCarrier = UserCarrier::where('email', $credentials['email'])->first();
    
        if (!$userCarrier || !Hash::check($credentials['password'], $userCarrier->password)) {
            return back()->withErrors(['email' => 'The provided credentials do not match our records.']);
        }
    
        // Verificar si el correo está confirmado
        if (!$userCarrier->email_verified_at) {
            return back()->withErrors(['email' => 'Please confirm your email before logging in.']);
        }
    
        // Iniciar sesión
        Auth::login($userCarrier, $request->boolean('remember'));
        $request->session()->regenerate();
    
        // Si no ha registrado un carrier, redirigir al formulario
        if (!$userCarrier->carrier_id) {
            return redirect()->route('user_carrier.complete_registration')
                ->with('status', 'Please complete your carrier registration.');
        }
    
        // Todo está bien, redirigir al dashboard
        return redirect()->route('user_carrier.dashboard');
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
        $userCarrier = UserCarrier::where('confirmation_token', $token)->first();
    
        if (!$userCarrier) {
            return redirect()->route('user_carrier.login')->withErrors(['email' => 'Invalid confirmation token.']);
        }
    
        // Confirmar el correo electrónico
        $userCarrier->update([
            'email_verified_at' => now(),
        ]);
    
        // No anular el token aquí si el carrier aún no está registrado
    
        // Iniciar sesión automáticamente al usuario confirmado
        Auth::guard('user_carrier')->login($userCarrier);
    
        // Redirige al formulario para completar el registro
        return redirect()->route('user_carrier.complete_registration')
            ->with('status', 'Please complete your carrier registration.');
    }
    
    
    public function showCompleteRegistrationForm(Request $request)
    {
        return view('auth.user_carrier.complete_registration');
    }

    public function completeRegistration(Request $request)
    {
        $validated = $request->validate([
            'carrier_name' => 'required|string|max:255',
            'carrier_address' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zipcode' => 'required|string|max:10',
            'ein_number' => 'required|string|max:255',
        ]);
    
        // Usuario actual autenticado
        $userCarrier = Auth::guard('user_carrier')->user();
    
        // Crear y asociar el Carrier
        $carrier = $userCarrier->carrier()->create($validated);
        $userCarrier->update(['carrier_id' => $carrier->id]);
    
        return redirect()->route('user_carrier.dashboard')->with('status', 'Carrier registration completed successfully.');
    }
    
    
    
}
