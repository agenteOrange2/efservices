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
    public function showRegistrationForm(Request $request, Carrier $carrier)
    {
        $token = $request->query('token');
        
        // Validar token y carrier
        if (!$this->validateTokenAndCarrier($carrier, $token)) {
            return redirect()->route('driver.register.error');
        }

        return view('auth.user_driver.register', compact('carrier'));
    }

    public function register(Request $request, $carrierSlug)
    {
        $carrier = Carrier::where('slug', $carrierSlug)
            ->where('referrer_token', $request->token)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'midde_name' => 'nullable|string',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'password' => 'required|min:8|confirmed',
            'license_number' => 'required|string',
            'phone' => 'required|string',
            'terms_accepted' => 'required|accepted'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password'])
        ]);

        $user->assignRole('driver');

        // Crear driver details básicos
        $user->driverDetails()->create([
            'carrier_id' => $carrier->id,
            'middle_name' => $validated['middle_name'],
            'last_name' => $validated['last_name'],
            'date_of_birth' => $validated['date_of_birth'],
            'license_number' => $validated['license_number'],
            'phone' => $validated['phone'],
            'status' => UserDriverDetail::STATUS_PENDING,
            'confirmation_token' => Str::random(32)
        ]);

        // Enviar email de confirmación
        Mail::to($user->email)->send(new DriverConfirmationMail($user->driverDetails));

        return redirect()->route('driver.registration.success');
    }

    public function confirmEmail($token)
    {
        $driver = UserDriverDetail::where('confirmation_token', $token)->firstOrFail();
        
        $driver->update([
            'confirmation_token' => null,
            'email_verified_at' => now()
        ]);

        return redirect()->route('login')
            ->with('success', 'Email confirmado. Por favor inicia sesión para completar tu registro.');
    }

    private function validateTokenAndCarrier(Carrier $carrier, $token)
    {
        if ($carrier->referrer_token !== $token || 
            $carrier->status !== Carrier::STATUS_ACTIVE ||
            $carrier->userDrivers()->count() >= ($carrier->membership->max_drivers ?? 1)) {
            return false;
        }
        return true;
    }
}
