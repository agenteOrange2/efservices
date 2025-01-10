<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCarrierRegistered
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::guard('user_carrier')->user();
        // Log::info("Middleware ejecutado para el usuario {$user->id}. Carrier ID: {$user->carrier_id}");
    
        if (!$user->carrier_id) {
            // Log::info("Redirigiendo al usuario {$user->id} a la página de completar registro.");
            return redirect()->route('user_carrier.complete_registration')
                ->with('status', 'Please complete your carrier registration.');
        }
    
        return $next($request);
    }
      
}
