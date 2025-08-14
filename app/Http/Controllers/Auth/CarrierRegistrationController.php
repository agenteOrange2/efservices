<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\UserCarrierDetail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\CarrierConfirmationMail;
use Spatie\Permission\Models\Role;

class CarrierRegistrationController extends Controller
{
    /**
     * Mostrar el formulario de registro inicial para carriers.
     */
    public function showRegisterForm()
    {
        return view('auth.user_carrier.register');
    }

    /**
     * Procesar el registro inicial del carrier.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:15',
            'job_position' => 'required|string|max:255',
        ]);

        try {
            // Crear el usuario
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => UserCarrierDetail::STATUS_ACTIVE,
            ]);

            // Asignar el rol automáticamente
            $user->assignRole('user_carrier');
            Log::info('Rol asignado al User.', ['user_id' => $user->id, 'role' => 'user_carrier']);

            // Crear el detalle del UserCarrier
            $userCarrierDetail = $user->carrierDetails()->create([
                'phone' => $validated['phone'],
                'job_position' => $validated['job_position'],
                'status' => UserCarrierDetail::STATUS_ACTIVE,
                'confirmation_token' => Str::random(32),
            ]);

            Log::info('UserCarrierDetail creado.', ['user_carrier_detail_id' => $userCarrierDetail->id]);

            // Enviar correo de confirmación
            Mail::to($user->email)->send(new CarrierConfirmationMail($userCarrierDetail));

            return redirect()->route('login')
                ->with('status', 'Registration successful. Please check your email to confirm.');
                
        } catch (\Exception $e) {
            Log::error('Error en registro de carrier', [
                'error' => $e->getMessage(),
                'email' => $validated['email']
            ]);
            
            return redirect()->back()
                ->withErrors(['error' => 'Registration failed. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Confirmar email del carrier.
     */
    public function confirmEmail($token)
    {
        // Busca el detalle del usuario carrier usando el token
        $userCarrierDetail = UserCarrierDetail::where('confirmation_token', $token)->first();

        if (!$userCarrierDetail) {
            return redirect()->route('login')->withErrors([
                'email' => 'Invalid or expired confirmation token.',
            ]);
        }

        // Actualiza el estado del correo electrónico y elimina el token
        $userCarrierDetail->update([
            'confirmation_token' => null,
            'status' => UserCarrierDetail::STATUS_ACTIVE,
        ]);

        // Autenticar al usuario
        auth()->login($userCarrierDetail->user);

        return redirect()->route('carrier.complete_registration')
            ->with('status', 'Your email has been confirmed. Please complete your registration.');
    }
}