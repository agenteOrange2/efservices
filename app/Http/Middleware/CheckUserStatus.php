<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        Log::info('CheckUserStatus middleware', [
            'user_id' => $user ? $user->id : null,
            'path' => $request->path(),
            'is_carrier' => $user ? $user->hasRole('user_carrier') : false
        ]);

        // Rutas públicas
        if ($request->is('/', 'login', 'user-carrier/register', 'user-carrier/confirm/*')) {
            return $next($request);
        }

        // Si no hay usuario autenticado
        if (!$user) {
            return redirect()->route('login')
                ->with('warning', 'Please login to continue.');
        }

        // Redirección basada en roles
        if ($user->hasRole('superadmin')) {
            if (!$request->is('admin*')) {
                return redirect()->route('admin.dashboard');
            }
        }

        if ($user->hasRole('user_carrier')) {
            // Si está accediendo a complete-registration, permitir
            if ($request->is('user-carrier/complete-registration*')) {
                return $next($request);
            }

            // Verificar email
            if ($user->carrierDetails && $user->carrierDetails->confirmation_token) {
                Auth::logout();
                return redirect()->route('login')
                    ->with('warning', 'Please confirm your email to continue.');
            }

            // Verificar registro completo
            if (!$user->carrierDetails || !$user->carrierDetails->carrier_id) {
                return redirect()->route('user_carrier.complete_registration')
                    ->with('warning', 'Please complete your carrier registration.');
            }

            // Verificar estado del carrier
            if ($user->carrierDetails->carrier && 
                $user->carrierDetails->carrier->status === Carrier::STATUS_PENDING) {
                Auth::logout();
                return redirect()->route('login')
                    ->with('warning', 'Your account is under review. We will notify you once approved.');
            }

            // Si todo está bien, redirigir al dashboard de carrier
            if (!$request->is('user-carrier*')) {
                return redirect()->route('user_carrier.dashboard');
            }
        }

        if ($user->hasRole('driver')) {
            if (!$request->is('driver*')) {
                return redirect()->route('driver.dashboard');
            }
        }

        return $next($request);
    }
}