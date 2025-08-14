<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Carrier;
use Illuminate\Http\Request;
use App\Models\UserCarrierDetail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CarrierAuthController extends Controller
{
    /**
     * Manejar el login de carriers con lógica específica.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
    
            // Verificar si el usuario tiene el rol de carrier
            if ($user instanceof \App\Models\User && $user->hasRole('user_carrier')) {
                return $this->handleCarrierLogin($user);
            }
    
            // Si es superadmin
            if ($user instanceof \App\Models\User && $user->hasRole('superadmin')) {
                return redirect()->route('admin.dashboard');
            }
        }
    
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Manejar la lógica específica de login para carriers.
     */
    private function handleCarrierLogin(User $user)
    {
        // Verificar si el usuario tiene carrier details
        if (!$user->carrierDetails || !$user->carrierDetails->carrier_id) {
            Log::info('Usuario carrier sin detalles completos', ['user_id' => $user->id]);
            
            return redirect()->route('carrier.complete_registration')
                ->with('warning', 'Please complete your registration.');
        }
        
        $carrier = $user->carrierDetails->carrier;
        
        // Verificar estado del carrier
        switch ($carrier->status) {
            case Carrier::STATUS_PENDING:
                Log::info('Carrier con estado pendiente', [
                    'user_id' => $user->id,
                    'carrier_id' => $carrier->id
                ]);
                
                return redirect()->route('carrier.confirmation')
                    ->with('warning', 'Your account is pending approval.');
                    
            case Carrier::STATUS_INACTIVE:
                Log::warning('Intento de login con carrier inactivo', [
                    'user_id' => $user->id,
                    'carrier_id' => $carrier->id
                ]);
                
                Auth::logout();
                return redirect()->route('login')
                    ->withErrors(['email' => 'Your account has been deactivated. Please contact support.']);
                    
            case Carrier::STATUS_ACTIVE:
                // Verificar estado de documentos
                if ($carrier->document_status === Carrier::DOCUMENT_STATUS_IN_PROGRESS) {
                    Log::info('Carrier con documentos en progreso', [
                        'user_id' => $user->id,
                        'carrier_id' => $carrier->id
                    ]);
                    
                    return redirect()->route('carrier.documents.index', $carrier->slug)
                        ->with('warning', 'Please complete your document submission.');
                }
                
                Log::info('Login exitoso de carrier', [
                    'user_id' => $user->id,
                    'carrier_id' => $carrier->id
                ]);
                
                return redirect()->route('carrier.dashboard');
                
            default:
                Log::error('Estado de carrier no reconocido', [
                    'user_id' => $user->id,
                    'carrier_id' => $carrier->id,
                    'status' => $carrier->status
                ]);
                
                return redirect()->route('carrier.confirmation')
                    ->with('error', 'Account status error. Please contact support.');
        }
    }

    /**
     * Manejar la autenticación después del login automático.
     */
    public function authenticated(Request $request, $user)
    {
        // Verificar si el usuario tiene el rol de carrier
        if ($user instanceof \App\Models\User && $user->hasRole('user_carrier')) {
            return $this->handleCarrierLogin($user);
        }

        // Si no es carrier, redirigir según el rol
        if ($user instanceof \App\Models\User && $user->hasRole('superadmin')) {
            return redirect()->route('admin.dashboard');
        }

        // Por defecto
        return redirect()->route('home');
    }

    /**
     * Obtener el estado actual del carrier para el usuario autenticado.
     */
    public function getCarrierStatus()
    {
        $user = Auth::user();
        
        if (!$user || !$user->hasRole('user_carrier')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        if (!$user->carrierDetails || !$user->carrierDetails->carrier_id) {
            return response()->json([
                'status' => 'incomplete',
                'step' => 'registration',
                'message' => 'Registration not completed'
            ]);
        }
        
        $carrier = $user->carrierDetails->carrier;
        
        return response()->json([
            'status' => $carrier->status,
            'document_status' => $carrier->document_status,
            'step' => $this->determineCurrentStep($carrier),
            'carrier_id' => $carrier->id,
            'carrier_slug' => $carrier->slug
        ]);
    }

    /**
     * Determinar el paso actual en el proceso de onboarding.
     */
    private function determineCurrentStep(Carrier $carrier): string
    {
        if ($carrier->status === Carrier::STATUS_ACTIVE) {
            return 'completed';
        }
        
        if ($carrier->document_status === Carrier::DOCUMENT_STATUS_IN_PROGRESS) {
            return 'documents';
        }
        
        if ($carrier->status === Carrier::STATUS_PENDING) {
            return 'approval';
        }
        
        return 'unknown';
    }
}