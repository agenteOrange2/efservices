<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Carrier;
use App\Helpers\Constants;
use App\Models\Membership;
use Illuminate\Support\Str;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use App\Models\CarrierDocument;
use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\CarrierService;
use App\Services\CarrierDocumentService;
use App\Traits\SendsCustomNotifications;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Admin\Carrier\NewCarrierNotification;
use App\Mail\PaymentValidatedMail;
use App\Mail\BankingRejectedMail;
use App\Mail\BankingPendingMail;
use Illuminate\Support\Facades\Mail;

class CarrierController extends Controller
{

    use SendsCustomNotifications;
    protected $documentService;
    protected $carrierService;

    public function __construct(
        CarrierDocumentService $documentService,
        CarrierService $carrierService
    ) {
        $this->documentService = $documentService;
        $this->carrierService = $carrierService;
    }

    /**
     * Mostrar todos los carriers.
     */
    public function index()
    {
        try {
            $carriers = $this->carrierService->getAllCarriers(['per_page' => 10]);
            return view('admin.carrier.index', compact('carriers'));
        } catch (\Exception $e) {
            Log::error('Error loading carriers index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Error loading carriers');
        }
    }

    /**
     * Mostrar el formulario para crear un nuevo carrier.
     */
    public function create()
    {
        $memberships = Membership::where('status', 1)->select('id', 'name')->get();
        $usStates = Constants::usStates();
        return view('admin.carrier.create', compact('memberships', 'usStates'));
    }

    /**
     * Guardar un nuevo carrier y asignar documentos base.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zipcode' => 'required|string|max:10',
            'ein_number' => 'required|string|max:255|unique:carriers,ein_number',
            'dot_number' => 'nullable|string|max:255|unique:carriers,dot_number',
            'mc_number' => 'nullable|string|max:255|unique:carriers,mc_number',
            'state_dot' => 'nullable|string|max:255',
            'ifta_account' => 'nullable|string|max:255',
            'logo_carrier' => 'nullable|image|max:2048',
            'id_plan' => 'required|exists:memberships,id',
            'status' => 'required|integer|in:' . implode(',', [
                Carrier::STATUS_INACTIVE,
                Carrier::STATUS_ACTIVE,
                Carrier::STATUS_PENDING,
                Carrier::STATUS_PENDING_VALIDATION
            ]),
        ]);

        try {
            // Crear el carrier usando el servicio
            $carrier = $this->carrierService->createCarrier($validated, $request->file('logo_carrier'));
            
            // Generar documentos base para el carrier
            $this->generateBaseDocuments($carrier);

            // Redirigir al tab de usuarios del carrier
            return redirect()
                ->route('admin.carrier.user_carriers.index', $carrier)
                ->with($this->sendNotification(
                    'success',
                    'Carrier creado exitosamente. Ahora puedes administrar los usuarios asociados.'
                ));
        } catch (\Exception $e) {
            Log::error('Error creating carrier', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $validated
            ]);
            
            return back()->withInput()->with('error', 'Error creating carrier: ' . $e->getMessage());
        }
    }

    /**
     * Generar documentos base para el carrier basado en los tipos predefinidos.
     */
    private function generateBaseDocuments(Carrier $carrier)
    {
        $documentTypes = DocumentType::all();

        foreach ($documentTypes as $type) {
            // Crear el CarrierDocument si no existe
            $carrierDocument = CarrierDocument::firstOrCreate([
                'carrier_id' => $carrier->id,
                'document_type_id' => $type->id,
            ], [
                'status' => CarrierDocument::STATUS_PENDING,
                'date' => now(),
            ]);

            // Verificar si el DocumentType tiene un archivo predeterminado
            $defaultMedia = $type->getFirstMedia('default_documents');

            // NO copiar el archivo predeterminado; se usa la referencia desde 'default_documents'.
            if ($defaultMedia && !$carrierDocument->getFirstMedia('carrier_documents')) {
                // Simplemente registramos que este documento tiene un predeterminado.
                $carrierDocument->update(['status' => CarrierDocument::STATUS_PENDING]);
            }
        }
    }


    public function documents(Carrier $carrier)
    {
        $documents = CarrierDocument::where('carrier_id', $carrier->id)->with('documentType')->get();
        $documentTypes = DocumentType::all(); // Aquí cargamos los tipos de documentos

        return view('admin.carrier.documents', compact('carrier', 'documents', 'documentTypes'));
    }
    
    /**
     * Mostrar información detallada de un carrier específico
     * Incluye datos principales, usuarios, conductores y documentos
     */
    public function show(Carrier $carrier)
    {
        try {
            // Obtener datos completos del carrier usando el servicio optimizado
            $carrierData = $this->carrierService->getCarrierWithDetails($carrier->id);
            
            // Extraer datos para la vista
            $carrier = $carrierData['carrier'];
            $userCarriers = $carrierData['userCarriers'];
            $drivers = $carrierData['drivers'];
            $documents = $carrierData['documents'];
            $pendingDocuments = $carrierData['pendingDocuments'];
            $approvedDocuments = $carrierData['approvedDocuments'];
            $rejectedDocuments = $carrierData['rejectedDocuments'];
            $missingDocumentTypes = $carrierData['missingDocumentTypes'];
            $stats = $carrierData['stats'];
            
            // Extraer detalles bancarios del carrier
            $bankingDetails = $carrier->bankingDetails;
            
            // Validar datos críticos antes de mostrar la vista
            if (!$carrier) {
                return back()->with('error', 'Carrier data could not be loaded');
            }
            
            return view('admin.carrier.show', compact(
                'carrier', 
                'userCarriers', 
                'drivers', 
                'documents',
                'pendingDocuments',
                'approvedDocuments',
                'rejectedDocuments',
                'missingDocumentTypes',
                'stats',
                'bankingDetails'
            ));
        } catch (\Exception $e) {
            Log::error('Error loading carrier details', [
                'carrier_id' => $carrier->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return back()->with($this->sendNotification(
                'error',
                'Error al cargar los detalles del carrier',
                'Por favor, inténtalo de nuevo o contacta al administrador si el problema persiste.'
            ));
        }
    }
    
    /**
     * Actualizar el estado de un documento de carrier.
     */
    public function updateDocumentStatus(Request $request, CarrierDocument $document)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);
        
        $document->update([
            'status' => $validated['status'],
        ]);
        
        return redirect()
            ->back()
            ->with($this->sendNotification(
                'success',
                'Document status updated successfully.'
            ));
    }
    
    /**
     * Eliminar la foto de perfil del carrier.
     */
    public function deletePhoto(Carrier $carrier)
    {
        try {
            // Eliminar la foto actual
            if ($carrier->hasMedia('logo_carrier')) {
                $carrier->getFirstMedia('logo_carrier')->delete();
            }
            
            return response()->json([
                'success' => true,
                'defaultPhotoUrl' => asset('images/default-carrier-logo.png')
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting carrier photo', [
                'carrier_id' => $carrier->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting photo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar el formulario para editar un carrier.
     */
    public function edit(Carrier $carrier)
    {
        $memberships = Membership::where('status', 1)->select('id', 'name')->get();
        $usStates = Constants::usStates();

        
        // Generar URL de referencia con el prefijo correcto
        $referralUrl = url("/driver/register/{$carrier->slug}?token={$carrier->referrer_token}");
        
        return view('admin.carrier.edit', compact('carrier', 'memberships', 'usStates','referralUrl'));
    }

    /**
     * Actualizar un carrier existente.
     */
    public function update(Request $request, Carrier $carrier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zipcode' => 'required|string|max:10',
            'ein_number' => 'required|string|max:255',
            'dot_number' => 'nullable|string|max:255',
            'mc_number' => 'nullable|string|max:255',
            'state_dot' => 'nullable|string|max:255',
            'ifta_account' => 'nullable|string|max:255',
            'logo_carrier' => 'nullable|image|max:2048',
            'id_plan' => 'required|exists:memberships,id',
            'status' => 'required|integer|in:' . implode(',', [
                Carrier::STATUS_INACTIVE,
                Carrier::STATUS_ACTIVE,
                Carrier::STATUS_PENDING,
                Carrier::STATUS_PENDING_VALIDATION
            ]),
            'referrer_token' => 'nullable|string|max:16|unique:carriers,referrer_token,' . $carrier->id,
        ]);

        try {
            // Actualizar el carrier usando el servicio
            $updatedCarrier = $this->carrierService->updateCarrier(
                $carrier->id, 
                $validated, 
                $request->file('logo_carrier')
            );

            return redirect()
                ->route('admin.carrier.user_carriers.index', $updatedCarrier)
                ->with($this->sendNotification(
                    'success',
                    'Carrier actualizado exitosamente.',
                    'Los cambios han sido guardados correctamente.'
                ));
        } catch (\Exception $e) {
            Log::error('Error updating carrier', [
                'carrier_id' => $carrier->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $validated
            ]);
            
            return back()->withInput()->with('error', 'Error updating carrier: ' . $e->getMessage());
        }
    }

    public function approveDefaultDocument(Request $request, Carrier $carrier, CarrierDocument $document)
    {
        $validated = $request->validate(['approved' => 'required|boolean']);

        $document->update([
            'status' => $validated['approved'] ? CarrierDocument::STATUS_APPROVED : CarrierDocument::STATUS_PENDING,
        ]);

        // Refresh the document to get the updated status
        $document->refresh();

        return response()->json([
            'message' => $validated['approved'] ? 'Default document approved' : 'Default document unapproved',
            'statusName' => $document->status_name,
        ]);
    }

    /**
     * Aprobar información bancaria del carrier.
     */
    public function approveBanking(Carrier $carrier)
    {
        try {
            $bankingDetails = $carrier->bankingDetails;
            
            if (!$bankingDetails) {
                return back()->with('error', 'No banking information found for this carrier.');
            }
            
            // Actualizar estado de información bancaria
            $bankingDetails->update(['status' => 'approved']);
            
            // Actualizar estado del carrier a activo
            $carrier->update(['status' => Carrier::STATUS_ACTIVE]);
            
            // Enviar email de notificación
            try {
                // Obtener el email del usuario principal del carrier
                $primaryUser = $carrier->userCarriers()->with('user')->first();
                $userEmail = $primaryUser ? $primaryUser->user->email : null;
                
                if ($userEmail) {
                    Mail::to($userEmail)->send(new PaymentValidatedMail($carrier));
                    Log::info('Payment validated email sent', [
                        'carrier_id' => $carrier->id,
                        'email' => $userEmail
                    ]);
                } else {
                    Log::warning('No primary user email found for carrier', [
                        'carrier_id' => $carrier->id
                    ]);
                }
            } catch (\Exception $emailError) {
                Log::error('Error sending payment validated email', [
                    'carrier_id' => $carrier->id,
                    'email' => $carrier->email,
                    'error' => $emailError->getMessage()
                ]);
            }
            
            Log::info('Banking information approved', [
                'carrier_id' => $carrier->id,
                'admin_user_id' => auth()->id(),
                'banking_details_id' => $bankingDetails->id
            ]);
            
            return back()->with($this->sendNotification(
                'success',
                'Banking information approved successfully.',
                'The carrier can now access their dashboard and has been notified by email.'
            ));
        } catch (\Exception $e) {
            Log::error('Error approving banking information', [
                'carrier_id' => $carrier->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Error approving banking information: ' . $e->getMessage());
        }
    }
    
    /**
     * Rechazar información bancaria del carrier.
     */
    public function rejectBanking(Request $request, Carrier $carrier)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);
        
        try {
            $bankingDetails = $carrier->bankingDetails;
            
            if (!$bankingDetails) {
                return back()->with('error', 'No banking information found for this carrier.');
            }
            
            // Actualizar estado de información bancaria
            $bankingDetails->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason
            ]);
            
            // Mantener el carrier en estado pending_validation
            $carrier->update(['status' => Carrier::STATUS_PENDING_VALIDATION]);
            
            // Enviar email de notificación
            try {
                // Obtener el email del usuario principal del carrier
                $primaryUser = $carrier->userCarriers()->with('user')->first();
                $userEmail = $primaryUser ? $primaryUser->user->email : null;
                
                if ($userEmail) {
                    Mail::to($userEmail)->send(new BankingRejectedMail($carrier, $request->rejection_reason));
                    Log::info('Banking rejected email sent', [
                        'carrier_id' => $carrier->id,
                        'email' => $userEmail
                    ]);
                } else {
                    Log::warning('No primary user email found for carrier', [
                        'carrier_id' => $carrier->id
                    ]);
                }
            } catch (\Exception $emailError) {
                Log::error('Error sending banking rejected email', [
                    'carrier_id' => $carrier->id,
                    'email' => $carrier->email,
                    'error' => $emailError->getMessage()
                ]);
            }
            
            Log::info('Banking information rejected', [
                'carrier_id' => $carrier->id,
                'admin_user_id' => auth()->id(),
                'banking_details_id' => $bankingDetails->id,
                'rejection_reason' => $request->rejection_reason
            ]);
            
            return back()->with($this->sendNotification(
                'warning',
                'Banking information rejected.',
                'The carrier has been notified by email and will need to resubmit their banking information.'
            ));
        } catch (\Exception $e) {
            Log::error('Error rejecting banking information', [
                'carrier_id' => $carrier->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Error rejecting banking information: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar información bancaria del carrier.
     */
    public function updateBanking(Request $request, Carrier $carrier)
    {
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_holder_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'routing_number' => 'required|string|max:20',
            'account_type' => 'required|in:checking,savings',
            'status' => 'required|in:pending,approved,rejected',
            'rejection_reason' => 'nullable|string|max:500'
        ]);
        
        try {
            $bankingDetails = $carrier->bankingDetails;
            
            if (!$bankingDetails) {
                return back()->with('error', 'No banking information found for this carrier.');
            }
            
            $oldStatus = $bankingDetails->status;
            $newStatus = $request->status;
            
            // Actualizar información bancaria
            $bankingDetails->update([
                'bank_name' => $request->bank_name,
                'account_holder_name' => $request->account_holder_name,
                'account_number' => $request->account_number,
                'routing_number' => $request->routing_number,
                'account_type' => $request->account_type,
                'status' => $newStatus,
                'rejection_reason' => $request->rejection_reason
            ]);
            
            // Manejar cambios de estado y envío de emails
            if ($oldStatus !== $newStatus) {
                // Obtener el email del usuario principal del carrier
                $primaryUser = $carrier->userCarriers()->with('user')->first();
                $userEmail = $primaryUser ? $primaryUser->user->email : null;
                $user = $primaryUser ? $primaryUser->user : null;
                
                if ($newStatus === 'approved') {
                    $carrier->update(['status' => Carrier::STATUS_ACTIVE]);
                    
                    // Enviar email de aprobación
                    if ($userEmail) {
                        try {
                            Mail::to($userEmail)->send(new PaymentValidatedMail($carrier, $user));
                            Log::info('Payment validated email sent after manual approval', [
                                'carrier_id' => $carrier->id,
                                'email' => $userEmail
                            ]);
                        } catch (\Exception $emailError) {
                            Log::error('Error sending payment validated email after manual approval', [
                                'carrier_id' => $carrier->id,
                                'email' => $userEmail,
                                'error' => $emailError->getMessage()
                            ]);
                        }
                    }
                } elseif ($newStatus === 'rejected' && $request->rejection_reason) {
                    $carrier->update(['status' => Carrier::STATUS_PENDING_VALIDATION]);
                    
                    // Enviar email de rechazo
                    if ($userEmail) {
                        try {
                            Mail::to($userEmail)->send(new BankingRejectedMail($carrier, $request->rejection_reason));
                            Log::info('Banking rejected email sent after manual rejection', [
                                'carrier_id' => $carrier->id,
                                'email' => $userEmail
                            ]);
                        } catch (\Exception $emailError) {
                            Log::error('Error sending banking rejected email after manual rejection', [
                                'carrier_id' => $carrier->id,
                                'email' => $userEmail,
                                'error' => $emailError->getMessage()
                            ]);
                        }
                    }
                } elseif ($newStatus === 'pending') {
                    $carrier->update(['status' => Carrier::STATUS_PENDING_VALIDATION]);
                    
                    // Enviar email de pending
                    if ($userEmail) {
                        try {
                            Mail::to($userEmail)->send(new BankingPendingMail($carrier, $user));
                            Log::info('Banking pending email sent after status change', [
                                'carrier_id' => $carrier->id,
                                'email' => $userEmail
                            ]);
                        } catch (\Exception $emailError) {
                            Log::error('Error sending banking pending email', [
                                'carrier_id' => $carrier->id,
                                'email' => $userEmail,
                                'error' => $emailError->getMessage()
                            ]);
                        }
                    }
                }
                
                if (!$userEmail) {
                    Log::warning('No primary user email found for carrier', [
                        'carrier_id' => $carrier->id,
                        'status_change' => $oldStatus . ' -> ' . $newStatus
                    ]);
                }
            }
            
            Log::info('Banking information updated', [
                'carrier_id' => $carrier->id,
                'admin_user_id' => auth()->id(),
                'banking_details_id' => $bankingDetails->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);
            
            return back()->with($this->sendNotification(
                'success',
                'Banking information updated successfully.',
                'The changes have been saved and the carrier has been notified if status changed.'
            ));
        } catch (\Exception $e) {
            Log::error('Error updating banking information', [
                'carrier_id' => $carrier->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Error updating banking information: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar un carrier.
     */
    public function destroy(Carrier $carrier)
    {
        $carrier->delete();

        return redirect()
            ->route('admin.carriers.index')
            ->with($this->sendNotification(
                'error',
                'Carrier eliminado exitosamente.',
                'El carrier y todos sus datos asociados han sido eliminados.'
            ));
    }
}
