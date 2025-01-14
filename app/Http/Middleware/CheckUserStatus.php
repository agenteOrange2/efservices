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
    
        // Rutas públicas actualizadas
        if (!$user && !$request->is('/', 'login', 'carrier/register', 'carrier/confirm/*')) {
            return redirect()->route('login')
                ->with('warning', 'Please login to continue.');
        }
    
        if ($user && $user->hasRole('user_carrier')) {
            // Si intenta acceder al admin
            if ($request->is('admin*')) {
                if (!$user->carrierDetails || !$user->carrierDetails->carrier_id) {
                    return redirect()->route('carrier.complete_registration')
                        ->with('warning', 'Please complete your registration first.');
                }
    
                if ($user->carrierDetails->carrier->status !== Carrier::STATUS_ACTIVE) {
                    return redirect()->route('carrier.confirmation')
                        ->with('warning', 'Your account is pending approval.');
                }
    
                return redirect()->route('carrier.dashboard')
                    ->with('warning', 'Access denied to admin area.');
            }
        }
    
        // Si es driver intentando acceder al admin
        if ($user && $user->hasRole('driver') && $request->is('admin*')) {
            return redirect()->route('driver.dashboard')
                ->with('warning', 'Access denied to admin area.');
        }
    
        return $next($request);
    }
}