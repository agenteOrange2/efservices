<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserCarrier;
use App\Models\UserDriver;
use Illuminate\Support\Facades\Hash;


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
        $guard = $request->routeIs('user_carrier.*') ? 'user_carrier' : ($request->routeIs('user_driver.*') ? 'user_driver' : 'web');

        Auth::shouldUse($guard);

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('/dashboard'); // Redirigir al dashboard según el guard
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
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

            UserCarrier::create($validated);

            return redirect()->route('user_carrier.login')->with('status', 'Registration successful. Please log in.');
        }

        if ($request->routeIs('user_driver.*')) {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:user_drivers,email',
                'password' => 'required|string|min:8|confirmed',
                'license_number' => 'required|string|max:50',
                'birth_date' => 'required|date',
                'phone' => 'required|string|max:15',
            ]);

            $validated['password'] = Hash::make($validated['password']);

            UserDriver::create($validated);

            return redirect()->route('user_driver.login')->with('status', 'Registration successful. Please log in.');
        }

        abort(404); // Mostrar error si no corresponde a ninguna ruta válida
    }

}