<?php

namespace App\Http\Controllers\Auth;

use App\Models\Carrier;
use Illuminate\Http\Request;
use App\Models\UserCarrierDetail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CarrierStatusController extends Controller
{
    /**
     * Mostrar la página de confirmación después del registro.
     */
    public function showConfirmation()
    {
        $user = Auth::user();
        
        if (!$user || !$user->hasRole('user_carrier')) {
            return redirect()->route('login');
        }

        // Verificar si el usuario tiene carrier details
        if (!$user->carrierDetails || !$user->carrierDetails->carrier_id) {
            Log::info('Usuario sin carrier details accediendo a confirmación', [
                'user_id' => $user->id
            ]);
            
            return redirect()->route('carrier.complete_registration')
                ->with('warning', 'Please complete your registration first.');
        }

        $carrier = $user->carrierDetails->carrier;
        $progress = $this->calculateRegistrationProgress($carrier);
        
        Log::info('Acceso a página de confirmación', [
            'user_id' => $user->id,
            'carrier_id' => $carrier->id,
            'carrier_status' => $carrier->status,
            'progress' => $progress
        ]);

        return view('carrier.auth.confirmation', compact('carrier', 'progress'));
    }

    /**
     * Mostrar la página de estado pendiente.
     */
    public function showPending()
    {
        $user = Auth::user();
        
        if (!$user || !$user->hasRole('user_carrier')) {
            return redirect()->route('login');
        }

        if (!$user->carrierDetails || !$user->carrierDetails->carrier_id) {
            return redirect()->route('carrier.complete_registration');
        }

        $carrier = $user->carrierDetails->carrier;
        
        // Solo mostrar esta página si el carrier está pendiente
        if ($carrier->status !== Carrier::STATUS_PENDING) {
            return $this->redirectBasedOnStatus($carrier);
        }

        $progress = $this->calculateRegistrationProgress($carrier);
        $estimatedTime = $this->getEstimatedApprovalTime($carrier);
        
        Log::info('Acceso a página de estado pendiente', [
            'user_id' => $user->id,
            'carrier_id' => $carrier->id
        ]);

        return view('carrier.auth.pending', compact('carrier', 'progress', 'estimatedTime'));
    }

    /**
     * Obtener el estado actual del proceso de registro.
     */
    public function getRegistrationStatus()
    {
        $user = Auth::user();
        
        if (!$user || !$user->hasRole('user_carrier')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!$user->carrierDetails || !$user->carrierDetails->carrier_id) {
            return response()->json([
                'status' => 'incomplete',
                'step' => 'registration',
                'progress' => 25,
                'message' => 'Registration not completed',
                'next_action' => route('carrier.complete_registration')
            ]);
        }

        $carrier = $user->carrierDetails->carrier;
        $progress = $this->calculateRegistrationProgress($carrier);
        $currentStep = $this->getCurrentStep($carrier);
        
        return response()->json([
            'status' => $carrier->status,
            'document_status' => $carrier->document_status,
            'step' => $currentStep,
            'progress' => $progress,
            'carrier_id' => $carrier->id,
            'carrier_slug' => $carrier->slug,
            'next_action' => $this->getNextAction($carrier),
            'estimated_time' => $this->getEstimatedApprovalTime($carrier)
        ]);
    }

    /**
     * Calcular el progreso del registro basado en el estado del carrier.
     */
    private function calculateRegistrationProgress(Carrier $carrier): int
    {
        $progress = 50; // Base: registro básico completado
        
        // Información de la empresa completada
        if ($carrier->company_name && $carrier->address) {
            $progress += 25;
        }
        
        // Documentos en progreso o completados
        if ($carrier->document_status === Carrier::DOCUMENT_STATUS_IN_PROGRESS) {
            $progress += 15;
        } elseif ($carrier->document_status === Carrier::DOCUMENT_STATUS_COMPLETED) {
            $progress += 25;
        }
        
        // Carrier aprobado
        if ($carrier->status === Carrier::STATUS_ACTIVE) {
            $progress = 100;
        }
        
        return min($progress, 100);
    }

    /**
     * Obtener el paso actual del proceso.
     */
    private function getCurrentStep(Carrier $carrier): string
    {
        if ($carrier->status === Carrier::STATUS_ACTIVE) {
            return 'completed';
        }
        
        if ($carrier->document_status === Carrier::DOCUMENT_STATUS_IN_PROGRESS) {
            return 'documents';
        }
        
        if ($carrier->document_status === Carrier::DOCUMENT_STATUS_COMPLETED) {
            return 'approval';
        }
        
        if ($carrier->status === Carrier::STATUS_PENDING) {
            return 'verification';
        }
        
        return 'registration';
    }

    /**
     * Obtener la siguiente acción recomendada.
     */
    private function getNextAction(Carrier $carrier): ?string
    {
        switch ($carrier->status) {
            case Carrier::STATUS_PENDING:
                if ($carrier->document_status === Carrier::DOCUMENT_STATUS_IN_PROGRESS) {
                    return route('carrier.documents.index', $carrier->slug);
                }
                return null; // Esperando aprobación
                
            case Carrier::STATUS_ACTIVE:
                return route('carrier.dashboard');
                
            case Carrier::STATUS_INACTIVE:
                return null; // Contactar soporte
                
            default:
                return route('carrier.complete_registration');
        }
    }

    /**
     * Obtener tiempo estimado de aprobación.
     */
    private function getEstimatedApprovalTime(Carrier $carrier): array
    {
        $createdAt = $carrier->created_at;
        $now = now();
        $daysSinceCreation = $createdAt->diffInDays($now);
        
        // Tiempo típico de aprobación: 2-5 días hábiles
        $estimatedDays = 5 - $daysSinceCreation;
        $estimatedDays = max(0, $estimatedDays);
        $estimatedDays = intval($estimatedDays); // Convertir a entero para eliminar decimales
        
        return [
            'days_since_creation' => intval($daysSinceCreation), // Asegurar que también sea entero
            'estimated_days_remaining' => $estimatedDays,
            'message' => $estimatedDays > 0 
                ? "Estimated approval in {$estimatedDays} business days"
                : "Your application is being reviewed and should be processed soon"
        ];
    }

    /**
     * Redirigir basado en el estado del carrier.
     */
    private function redirectBasedOnStatus(Carrier $carrier)
    {
        switch ($carrier->status) {
            case Carrier::STATUS_ACTIVE:
                if ($carrier->document_status === Carrier::DOCUMENT_STATUS_IN_PROGRESS) {
                    return redirect()->route('carrier.documents.index', $carrier->slug);
                }
                return redirect()->route('carrier.dashboard');
                
            case Carrier::STATUS_INACTIVE:
                return redirect()->route('carrier.inactive');
                
            case Carrier::STATUS_PENDING_VALIDATION:
                return redirect()->route('carrier.pending-validation');
                    
            default:
                return redirect()->route('carrier.confirmation');
        }
    }

    /**
     * Mostrar página de validación pendiente.
     */
    public function pendingValidation()
    {
        $user = Auth::user();
        
        if (!$user || !$user->hasRole('user_carrier')) {
            return redirect()->route('login');
        }

        $carrier = $user->carrierDetails ? $user->carrierDetails->carrier : null;
        
        if (!$carrier || $carrier->status !== Carrier::STATUS_PENDING) {
            return redirect()->route('carrier.dashboard');
        }
        
        $progress = $this->calculateRegistrationProgress($carrier);
        $estimatedTime = $this->getEstimatedApprovalTime($carrier);
        
        return view('carrier.auth.pending-validation', compact('carrier', 'progress', 'estimatedTime'));
    }

    /**
     * Mostrar página de ayuda y soporte.
     */
    public function showSupport()
    {
        $user = Auth::user();
        
        if (!$user || !$user->hasRole('user_carrier')) {
            return redirect()->route('login');
        }

        $carrier = $user->carrierDetails ? $user->carrierDetails->carrier : null;
        
        return view('carrier.auth.support', compact('carrier'));
    }

    /**
     * Enviar solicitud de soporte.
     */
    public function submitSupportRequest(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'priority' => 'required|in:low,medium,high'
        ]);

        $user = Auth::user();
        $carrier = $user->carrierDetails ? $user->carrierDetails->carrier : null;

        // Aquí se podría integrar con un sistema de tickets
        Log::info('Solicitud de soporte enviada', [
            'user_id' => $user->id,
            'carrier_id' => $carrier ? $carrier->id : null,
            'subject' => $request->input('subject'),
            'priority' => $request->input('priority')
        ]);

        return back()->with('success', 'Your support request has been submitted. We will contact you soon.');
    }

    /**
     * Show the inactive status page
     */
    public function showInactive()
    {
        $user = Auth::user();
        
        if (!$user || !$user->hasRole('user_carrier')) {
            return redirect()->route('login');
        }

        $carrier = $user->carrierDetails ? $user->carrierDetails->carrier : null;
        
        if (!$carrier) {
            return redirect()->route('carrier.wizard.step1');
        }
        
        // Only show this page if carrier is actually inactive
        if ($carrier->status !== Carrier::STATUS_INACTIVE) {
            return $this->redirectBasedOnStatus($carrier);
        }
        
        // Log user action
        Log::info('Carrier viewed inactive status page', [
            'user_id' => $user->id,
            'carrier_id' => $carrier->id,
            'carrier_status' => $carrier->status
        ]);
        
        return view('carrier.auth.inactive-status', compact('carrier'));
    }
    
    /**
     * Show the payment validated page
     */
    public function showPaymentValidated()
    {
        $user = Auth::user();
        
        if (!$user || !$user->hasRole('user_carrier')) {
            return redirect()->route('login');
        }

        $carrier = $user->carrierDetails ? $user->carrierDetails->carrier : null;
        
        if (!$carrier) {
            return redirect()->route('carrier.wizard.step1');
        }
        
        // This page should only be shown for active carriers who just completed validation
        if ($carrier->status !== Carrier::STATUS_ACTIVE) {
            return $this->redirectBasedOnStatus($carrier);
        }
        
        // Log user action
        Log::info('Carrier viewed payment validated page', [
            'user_id' => $user->id,
            'carrier_id' => $carrier->id,
            'carrier_status' => $carrier->status
        ]);
        
        return view('carrier.auth.payment-validated', compact('carrier'));
    }
    
    /**
     * Handle reactivation request for inactive carriers
     */
    public function requestReactivation(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || !$user->hasRole('user_carrier')) {
            return redirect()->route('login');
        }

        $carrier = $user->carrierDetails ? $user->carrierDetails->carrier : null;
        
        if (!$carrier || $carrier->status !== Carrier::STATUS_INACTIVE) {
            return redirect()->route('carrier.status');
        }
        
        $request->validate([
            'reason' => 'required|string|max:1000',
            'additional_info' => 'nullable|string|max:2000'
        ]);
        
        // Log reactivation request
        Log::info('Carrier requested reactivation', [
            'user_id' => $user->id,
            'carrier_id' => $carrier->id,
            'reason' => $request->reason,
            'additional_info' => $request->additional_info
        ]);
        
        // Here you would typically:
        // 1. Create a reactivation request record
        // 2. Send notification to admin
        // 3. Send confirmation email to carrier
        
        return redirect()->route('carrier.inactive')
            ->with('success', 'Your reactivation request has been submitted successfully. Our team will review it and contact you within 2-3 business days.');
    }
}