<?php

namespace App\Http\Controllers\Auth;

use App\Models\Carrier;
use App\Models\CarrierDocument;
use Illuminate\Http\Request;
use App\Models\UserCarrierDetail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\CarrierDocumentService;

class CarrierDocumentController extends Controller
{
    protected $carrierDocumentService;

    public function __construct(CarrierDocumentService $carrierDocumentService)
    {
        $this->carrierDocumentService = $carrierDocumentService;
    }

    /**
     * Mostrar la página de documentos para el carrier.
     */
    public function index($carrierSlug)
    {
        $carrier = $this->findCarrierBySlug($carrierSlug);
        
        if (!$this->canAccessCarrier($carrier)) {
            Log::warning('Acceso no autorizado a documentos de carrier', [
                'user_id' => Auth::id(),
                'carrier_slug' => $carrierSlug,
                'carrier_id' => $carrier ? $carrier->id : null
            ]);
            
            return redirect()->route('login')
                ->withErrors(['access' => 'You do not have permission to access this carrier.']);
        }

        // Obtener documentos mapeados con estado mejorado
        $mappedDocuments = $this->getMappedDocumentsWithStatus($carrier);
        
        // Calcular progreso y estadísticas
        $progress = $this->carrierDocumentService->getDocumentProgress($carrier);
        
        // Preparar estadísticas para filtros
        $documentStats = $this->calculateDocumentStats($mappedDocuments);
        
        Log::info('Acceso a documentos de carrier', [
            'user_id' => Auth::id(),
            'carrier_id' => $carrier->id,
            'document_count' => count($mappedDocuments),
            'progress_percentage' => $progress['progress_percentage']
        ]);

        return view('carrier.documents.index', compact(
            'carrier', 
            'mappedDocuments', 
            'progress', 
            'documentStats'
        ));
    }

    /**
     * Obtener documentos mapeados con estado mejorado
     */
    private function getMappedDocumentsWithStatus($carrier)
    {
        $documentTypes = \App\Models\DocumentType::all();
        $carrierDocuments = $carrier->documents()->with('documentType')->get();
        
        $mappedDocuments = [];
        
        foreach ($documentTypes as $type) {
            $carrierDocument = $carrierDocuments->where('document_type_id', $type->id)->first();
            
            // Determinar el estado del documento
            $status = $this->determineDocumentStatus($carrierDocument, $type);
            
            // Verificar si tiene archivo
            $hasFile = $carrierDocument && $carrierDocument->getFirstMedia('carrier_documents');
            
            // Verificar si tiene documento por defecto disponible
            $hasDefault = $type->getFirstMedia('default_documents') !== null;
            
            $mappedDocuments[] = [
                'type' => $type,
                'document' => $carrierDocument,
                'status' => $status,
                'has_file' => $hasFile,
                'has_default' => $hasDefault,
            ];
        }
        
        return $mappedDocuments;
    }

    /**
     * Determinar el estado del documento
     */
    private function determineDocumentStatus($carrierDocument, $documentType)
    {
        if (!$carrierDocument) {
            // Si no existe el documento del carrier, verificar si hay uno por defecto
            $hasDefault = $documentType->getFirstMedia('default_documents') !== null;
            return $hasDefault ? 'default-available' : 'missing';
        }
        
        // Si existe el documento del carrier
        $hasFile = $carrierDocument->getFirstMedia('carrier_documents');
        
        if (!$hasFile) {
            // No tiene archivo, verificar si hay uno por defecto
            $hasDefault = $documentType->getFirstMedia('default_documents') !== null;
            return $hasDefault ? 'default-available' : 'missing';
        }
        
        // Tiene archivo, verificar el estado
        switch ($carrierDocument->status) {
            case \App\Models\CarrierDocument::STATUS_APPROVED:
                return 'uploaded';
            case \App\Models\CarrierDocument::STATUS_REJECTED:
                return 'rejected'; // Rechazado tiene su propio estado
            case \App\Models\CarrierDocument::STATUS_IN_PROCESS:
            case \App\Models\CarrierDocument::STATUS_PENDING:
            default:
                return 'pending';
        }
    }

    /**
     * Calcular estadísticas de documentos para filtros
     */
    private function calculateDocumentStats($mappedDocuments)
    {
        $stats = [
            'all' => count($mappedDocuments),
            'uploaded' => 0,
            'pending' => 0,
            'missing' => 0,
            'rejected' => 0,
            'default-available' => 0,
            'mandatory' => 0,
            'optional' => 0
        ];
        
        foreach ($mappedDocuments as $document) {
            $stats[$document['status']]++;
            
            if ($document['type']->requirement) {
                $stats['mandatory']++;
            } else {
                $stats['optional']++;
            }
        }
        
        return $stats;
    }

    /**
     * Subir un documento para el carrier.
     */
    public function upload(Request $request, $carrierSlug)
    {
        $carrier = $this->findCarrierBySlug($carrierSlug);
        
        if (!$this->canAccessCarrier($carrier)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to upload documents for this carrier.'
            ], 403);
        }

        $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
        ]);

        try {
            $documentType = \App\Models\DocumentType::findOrFail($request->input('document_type_id'));
            
            $result = $this->carrierDocumentService->uploadDocument(
                $carrier,
                $documentType,
                $request->file('file')
            );

            Log::info('Documento subido exitosamente', [
                'user_id' => Auth::id(),
                'carrier_id' => $carrier->id,
                'document_type_id' => $request->input('document_type_id'),
                'file_name' => $request->file('file')->getClientOriginalName()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully.',
                'document' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Error al subir documento', [
                'user_id' => Auth::id(),
                'carrier_id' => $carrier->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error uploading document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Acepta un documento por defecto para el carrier.
     */
    public function toggleDefaultDocument(Request $request, $carrierSlug, $documentType)
    {
        $carrier = $this->findCarrierBySlug($carrierSlug);
        
        if (!$this->canAccessCarrier($carrier)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to modify documents for this carrier.'
            ], 403);
        }

        try {
            $result = $this->carrierDocumentService->toggleDefaultDocument(
                $carrier,
                $documentType
            );

            Log::info('Documento por defecto aceptado', [
                'user_id' => Auth::id(),
                'carrier_id' => $carrier->id,
                'document_type_id' => $documentType
            ]);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'document' => $result['document']
            ]);

        } catch (\Exception $e) {
            Log::error('Error al aceptar documento por defecto', [
                'user_id' => Auth::id(),
                'carrier_id' => $carrier->id,
                'document_type_id' => $documentType,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un documento.
     */
    public function deleteDocument(Request $request, $carrierSlug)
    {
        $carrier = $this->findCarrierBySlug($carrierSlug);
        
        if (!$this->canAccessCarrier($carrier)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete documents for this carrier.'
            ], 403);
        }

        $request->validate([
            'media_id' => 'required|exists:media,id',
        ]);

        try {
            $result = $this->carrierDocumentService->deleteDocument(
                $carrier,
                $request->input('media_id')
            );

            Log::info('Documento eliminado', [
                'user_id' => Auth::id(),
                'carrier_id' => $carrier->id,
                'media_id' => $request->input('media_id')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar documento', [
                'user_id' => Auth::id(),
                'carrier_id' => $carrier->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error deleting document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener el progreso de documentos del carrier.
     */
    public function getDocumentProgress($carrierSlug)
    {
        $carrier = $this->findCarrierBySlug($carrierSlug);
        
        if (!$this->canAccessCarrier($carrier)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        $progress = $this->carrierDocumentService->getDocumentProgress($carrier);

        return response()->json([
            'success' => true,
            'progress' => $progress
        ]);
    }

    /**
     * Buscar carrier por slug.
     */
    private function findCarrierBySlug($slug)
    {
        return Carrier::where('slug', $slug)->first();
    }

    /**
     * Mostrar/descargar un documento específico.
     */
    public function viewDocument($carrierSlug, CarrierDocument $document)
    {
        $carrier = $this->findCarrierBySlug($carrierSlug);
        
        if (!$this->canAccessCarrier($carrier)) {
            Log::warning('Acceso no autorizado a documento de carrier', [
                'user_id' => Auth::id(),
                'carrier_slug' => $carrierSlug,
                'document_id' => $document->id
            ]);
            
            abort(403, 'You do not have permission to access this document.');
        }

        // Verificar que el documento pertenece al carrier
        if ($document->carrier_id !== $carrier->id) {
            Log::warning('Intento de acceso a documento de otro carrier', [
                'user_id' => Auth::id(),
                'carrier_slug' => $carrierSlug,
                'document_id' => $document->id,
                'document_carrier_id' => $document->carrier_id,
                'expected_carrier_id' => $carrier->id
            ]);
            
            abort(404, 'Document not found.');
        }

        // Obtener el archivo usando Spatie Media Library
        $mediaFile = $document->getFirstMedia('carrier_documents');
        
        if (!$mediaFile) {
            Log::warning('Intento de acceso a documento sin archivo', [
                'user_id' => Auth::id(),
                'document_id' => $document->id
            ]);
            
            abort(404, 'Document file not found.');
        }

        // Verificar que el archivo existe físicamente
        if (!file_exists($mediaFile->getPath())) {
            Log::error('Archivo de documento no encontrado en storage', [
                'user_id' => Auth::id(),
                'document_id' => $document->id,
                'media_path' => $mediaFile->getPath()
            ]);
            
            abort(404, 'Document file not found in storage.');
        }

        Log::info('Acceso a documento de carrier', [
            'user_id' => Auth::id(),
            'carrier_slug' => $carrierSlug,
            'document_id' => $document->id,
            'media_path' => $mediaFile->getPath(),
            'media_name' => $mediaFile->name
        ]);

        // Obtener el path completo del archivo
        $filePath = $mediaFile->getPath();
        
        // Obtener el nombre original del archivo
        $fileName = $mediaFile->name;
        
        // Determinar el tipo MIME
        $mimeType = $mediaFile->mime_type;
        
        // Retornar el archivo como respuesta para visualización inline
        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $fileName . '"'
        ]);
    }

    /**
     * Verificar si el usuario puede acceder al carrier.
     */
    private function canAccessCarrier($carrier)
    {
        if (!$carrier) {
            return false;
        }

        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        // Verificar si es superadmin
        if ($user->hasRole('superadmin')) {
            return true;
        }

        // Verificar si es el carrier owner
        if ($user->carrierDetails && $user->carrierDetails->carrier_id === $carrier->id) {
            return true;
        }

        // Verificar si es un carrier recién registrado (usando sesión)
        if (session('newly_registered_carrier_id') === $carrier->id) {
            return true;
        }

        return false;
    }

    /**
     * Manejar el "skip for now" - permitir acceso temporal al dashboard
     */
    public function skipDocuments($carrierSlug)
    {
        $carrier = $this->findCarrierBySlug($carrierSlug);
        
        if (!$this->canAccessCarrier($carrier)) {
            return redirect()->route('login')
                ->withErrors(['access' => 'You do not have permission to access this carrier.']);
        }

        // Establecer sesión temporal para permitir acceso al dashboard
        session(['skip_documents_' . $carrier->id => true]);

        Log::info('Carrier skipped documents temporarily', [
            'user_id' => Auth::id(),
            'carrier_id' => $carrier->id,
            'carrier_slug' => $carrierSlug
        ]);

        return redirect()->route('carrier.dashboard')
            ->with('info', 'You can upload your documents later from the dashboard. We\'ll remind you about pending documents.');
    }
}