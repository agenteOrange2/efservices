<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureCarrierRegistered
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $userCarrier = Auth::guard('user_carrier')->user();
    
        if (!$userCarrier || !$userCarrier->carrier_id) {
            return redirect()->route('user_carrier.complete_registration')
                ->with('status', 'You must complete your carrier registration first.');
        }
    
        return $next($request);
    }
    
}
