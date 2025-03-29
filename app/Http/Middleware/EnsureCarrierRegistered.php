<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\UserCarrierDetail;
use Symfony\Component\HttpFoundation\Response;

class EnsureCarrierRegistered
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    /*
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
    */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        Log::info('EnsureCarrierRegistered middleware ejecutado', [
            'user_id' => $user->id,
            'path' => $request->path(),
            'has_carrier_details' => $user->carrierDetails ? 'sí' : 'no'
        ]);

        // Verificar primero si el usuario tiene detalles de carrier
        if (!$user->carrierDetails) {
            Log::warning('Usuario sin detalles de carrier', ['user_id' => $user->id]);
            return redirect()->route('carrier.complete_registration')
                ->with('warning', 'Necesitas completar tu registro primero.');
        }

        // Añadir logs detallados sobre el estado del usuario
        Log::info('Estado del usuario carrier', [
            'user_id' => $user->id,
            'carrier_detail_status' => $user->carrierDetails->status,
            'carrier_id' => $user->carrierDetails->carrier_id,
            'carrier_status' => $user->carrierDetails->carrier ? $user->carrierDetails->carrier->status : 'N/A'
        ]);

        // Verificar el estado del usuario carrier (no del carrier)
        if ($user->carrierDetails->status === UserCarrierDetail::STATUS_PENDING) {
            Log::warning('Usuario carrier con estado pendiente', ['user_id' => $user->id]);
            return redirect()->route('carrier.confirmation')
                ->with('warning', 'Tu cuenta de usuario está en revisión. El acceso está restringido hasta la aprobación.');
        }

        // Verificar el estado del carrier
        if ($user->carrierDetails->carrier && $user->carrierDetails->carrier->status !== Carrier::STATUS_ACTIVE) {
            Log::warning('Carrier no activo', [
                'user_id' => $user->id, 
                'carrier_id' => $user->carrierDetails->carrier->id,
                'carrier_status' => $user->carrierDetails->carrier->status
            ]);
            return redirect()->route('carrier.confirmation')
                ->with('warning', 'La empresa de transporte asociada está en revisión. El acceso está restringido hasta la aprobación.');
        }

        Log::info('Usuario carrier pasa todas las validaciones', ['user_id' => $user->id]);
        return $next($request);
    }
      
}
