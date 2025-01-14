<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Carrier;
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
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Verificar si el usuario tiene un Carrier y su estado es pendiente
        if ($user->carrierDetails && $user->carrierDetails->carrier->status === Carrier::STATUS_PENDING) {
            return redirect()->route('user_carrier.confirmation')
                ->with('status', 'Your account is under review. Access to the admin area is restricted until approval.');
        }

        return $next($request);
    }
      
}
