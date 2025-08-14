<?php

namespace App\Http\Controllers\Carrier;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\CarrierDocument;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\CarrierDocumentService;

class DocumentController extends Controller
{
    protected $documentService;

    public function __construct(CarrierDocumentService $documentService)
    {
        $this->documentService = $documentService;
    }
    
    /**
     * Mapea los tipos de documentos con sus documentos subidos
     *
     * @param \Illuminate\Database\Eloquent\Collection $documentTypes
     * @param \Illuminate\Database\Eloquent\Collection $uploadedDocuments
     * @return \Illuminate\Support\Collection
     */
    protected function mapDocuments($documentTypes, $uploadedDocuments)
    {
        return $documentTypes->map(function ($type) use ($uploadedDocuments) {
            $uploaded = $uploadedDocuments->firstWhere('document_type_id', $type->id);
            return [
                'type' => $type,
                'document' => $uploaded,
                'status_name' => $uploaded ? $uploaded->status_name : 'Not Uploaded',
                'file_url' => $uploaded ? $uploaded->getFirstMediaUrl('carrier_documents') : null,
            ];
        });
    }
    
    /**
     * Verifica si el usuario puede acceder a un carrier específico
     * Implementa lógica especial para carriers recién registrados
     *
     * @param int $carrierId ID del carrier al que se intenta acceder
     * @return bool
     */
    protected function canAccessCarrier($carrierId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        // Si la sesión tiene algún indicador temporal de registro reciente
        $recentlyRegisteredCarrierId = session('recently_registered_carrier_id');
        if ($recentlyRegisteredCarrierId && $recentlyRegisteredCarrierId == $carrierId) {
            \Illuminate\Support\Facades\Log::info('Permitiendo acceso a carrier recién registrado', [
                'user_id' => $user->id,
                'carrier_id' => $carrierId,
                'from_session' => true
            ]);
            return true;
        }
        
        // Verificar por la relación del usuario con el carrier
        if ($user->carrierDetails && $user->carrierDetails->carrier_id == $carrierId) {
            return true;
        }
        
        // Log detallado del intento
        \Illuminate\Support\Facades\Log::info('Verificando acceso a carrier', [
            'user_id' => $user->id,
            'carrier_id_solicitado' => $carrierId,
            'carrier_id_usuario' => $user->carrierDetails ? $user->carrierDetails->carrier_id : null,
            'tiene_carrier_details' => $user->carrierDetails ? 'sí' : 'no'
        ]);
        
        return false;
    }

    public function index(Carrier $carrier)
    {
        $user = Auth::user();

        // Agregamos logs para depurar el problema en producción
        \Illuminate\Support\Facades\Log::info('Acceso a documentos de carrier', [
            'user_id' => $user->id,
            'carrier_id_de_url' => $carrier->id,
            'carrier_slug' => $carrier->slug,
            'user_carrier_id' => $user->carrierDetails ? $user->carrierDetails->carrier_id : null,
            'carrier_details_existe' => $user->carrierDetails ? 'SÍ' : 'NO'
        ]);

        // Almacenar temporalmente el ID del carrier para mantenerlo como "recién registrado"
        // durante esta sesión HTTP para facilitar acceso a documentos recién después del registro
        if (!$user->carrierDetails || ($user->carrierDetails && !$user->carrierDetails->carrier_id)) {
            session(['recently_registered_carrier_id' => $carrier->id]);
            \Illuminate\Support\Facades\Log::info('Marcando carrier como recién registrado en sesión', [
                'user_id' => $user->id,
                'carrier_id' => $carrier->id
            ]);
        }

        // Verificar acceso al carrier
        if (!$this->canAccessCarrier($carrier->id)) {
            \Illuminate\Support\Facades\Log::warning('Intento de acceso no autorizado a documentos de carrier', [
                'user_id' => $user->id,
                'carrier_solicitado' => $carrier->id,
                'carrier_del_usuario' => $user->carrierDetails ? $user->carrierDetails->carrier_id : null
            ]);
            abort(403, 'No tienes permiso para ver estos documentos');
        }

        $documentTypes = DocumentType::all();
        $uploadedDocuments = CarrierDocument::where('carrier_id', $carrier->id)->get();

        $documents = $this->mapDocuments($documentTypes, $uploadedDocuments);

        return view('auth.user_carrier.documents.index', compact('carrier', 'documents'));
    }

    // DocumentController.php

    public function toggleDefaultDocument(Request $request, Carrier $carrier)
    {
        $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'approved' => 'required|boolean'
        ]);
        
        $documentType = DocumentType::findOrFail($request->document_type_id);
    
        $document = CarrierDocument::firstOrCreate(
            [
                'carrier_id' => $carrier->id,
                'document_type_id' => $documentType->id,
            ],
            [
                'status' => CarrierDocument::STATUS_PENDING,
                'date' => now(),
            ]
        );
    
        $document->status = $request->approved ? 
            CarrierDocument::STATUS_APPROVED : 
            CarrierDocument::STATUS_PENDING;
        $document->save();
    
        return response()->json([
            'success' => true,
            'status' => $document->status,
            'statusName' => $document->status_name
        ]);
    }

    public function upload(Request $request, Carrier $carrier, DocumentType $documentType)
    {
        $user = Auth::user();
        
        // Verificar acceso al carrier
        if (!$this->canAccessCarrier($carrier->id)) {
            \Illuminate\Support\Facades\Log::warning('Intento de subir documento no autorizado', [
                'user_id' => $user->id,
                'carrier_solicitado' => $carrier->id,
                'carrier_del_usuario' => $user->carrierDetails ? $user->carrierDetails->carrier_id : null
            ]);
            abort(403, 'No tienes permiso para subir documentos a este carrier');
        }

        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,png|max:2048'
        ]);

        $carrierDocument = CarrierDocument::firstOrCreate(
            [
                'carrier_id' => $carrier->id,
                'document_type_id' => $documentType->id,
            ],
            [
                'status' => CarrierDocument::STATUS_PENDING,
                'date' => now(),
            ]
        );

        if ($request->hasFile('document')) {
            $carrierDocument->clearMediaCollection('carrier_documents');
            $carrierDocument->addMediaFromRequest('document')
                ->toMediaCollection('carrier_documents');
        }

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function skipDocuments(Carrier $carrier)
    {
        $user = Auth::user();
        
        // Verificar acceso al carrier
        if (!$this->canAccessCarrier($carrier->id)) {
            \Illuminate\Support\Facades\Log::warning('Intento no autorizado de omitir documentos', [
                'user_id' => $user->id,
                'carrier_solicitado' => $carrier->id,
                'carrier_del_usuario' => $user->carrierDetails ? $user->carrierDetails->carrier_id : null
            ]);
            abort(403, 'No tienes permiso para omitir documentos de este carrier');
        }

        $carrier->update(['document_status' => 'skipped']);

        return redirect()->route('carrier.confirmation')
            ->with('status', 'You can upload your documents later from your dashboard.');
    }

    public function complete(Carrier $carrier)
    {
        $user = Auth::user();
        
        // Verificar acceso al carrier
        if (!$this->canAccessCarrier($carrier->id)) {
            \Illuminate\Support\Facades\Log::warning('Intento no autorizado de completar documentos', [
                'user_id' => $user->id,
                'carrier_solicitado' => $carrier->id,
                'carrier_del_usuario' => $user->carrierDetails ? $user->carrierDetails->carrier_id : null
            ]);
            abort(403, 'No tienes permiso para completar documentos de este carrier');
        }

        $carrier->update(['document_status' => 'completed']);

        return redirect()->route('carrier.confirmation')
            ->with('status', 'Your documents have been submitted for review.');
    }
}