<?php

namespace App\Http\Controllers\Carrier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Carrier;

class CarrierDashboardController extends Controller
{
    /**
     * Mostrar el dashboard del carrier
     */
    public function index()
    {
        $user = Auth::user();
        
        Log::info('CarrierDashboardController: Usuario accedió al dashboard', [
            'user_id' => $user ? $user->id : null,
            'user_email' => $user ? $user->email : null,
            'has_carrier_details' => $user && $user->carrierDetails ? 'yes' : 'no',
            'carrier_id' => $user && $user->carrierDetails ? $user->carrierDetails->carrier_id : null,
            'session_id' => request()->session()->getId(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referer' => request()->header('referer'),
            'timestamp' => now()->toISOString()
        ]);
        
        // Verificar si el usuario tiene carrierDetails
        if (!$user->carrierDetails) {
            return redirect()->route('carrier.wizard.step2')
                ->with('error', 'Debe completar el proceso de registro primero.');
        }
        
        // Verificar si tiene carrier_id
        if (!$user->carrierDetails->carrier_id) {
            return redirect()->route('carrier.wizard.step2')
                ->with('error', 'Debe completar el proceso de registro primero.');
        }
        
        // Obtener el carrier
        $carrier = $user->carrierDetails->carrier;
        
        if (!$carrier) {
            return redirect()->route('carrier.wizard.step2')
                ->with('error', 'No se encontró información del carrier.');
        }
        
        // Verificar el estado del carrier y redirigir según corresponda
        switch ($carrier->status) {
            case Carrier::STATUS_PENDING:
                return redirect()->route('carrier.pending.validation')
                    ->with('info', 'Su solicitud está pendiente de validación.');
                    
            case Carrier::STATUS_REJECTED:
                return redirect()->route('carrier.confirmation')
                    ->with('error', 'Su solicitud ha sido rechazada.');
                    
            case Carrier::STATUS_ACTIVE:
                // Verificar si los documentos están completos
                if (!$carrier->documents_complete) {
                    return redirect()->route('carrier.documents.index')
                        ->with('warning', 'Debe completar la carga de documentos.');
                }
                break;
                
            default:
                return redirect()->route('carrier.wizard.step2')
                    ->with('error', 'Estado del carrier no válido.');
        }
        
        // Si llegamos aquí, el carrier está activo y puede ver el dashboard
        return view('carrier.dashboard', compact('carrier'));
    }
}