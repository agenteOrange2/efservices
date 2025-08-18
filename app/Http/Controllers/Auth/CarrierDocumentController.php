<?php

namespace App\Http\Controllers\Auth;

use App\Models\Carrier;
use Illuminate\Http\Request;
use App\Models\UserCarrierDetail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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

        // Obtener documentos mapeados
        $mappedDocuments = $this->carrierDocumentService->getMappedDocuments($carrier);
        
        Log::info('Acceso a documentos de carrier', [
            'user_id' => Auth::id(),
            'carrier_id' => $carrier->id,
            'document_count' => count($mappedDocuments)
        ]);

        return view('carrier.documents.index', compact('carrier', 'mappedDocuments'));
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
            $result = $this->carrierDocumentService->uploadDocument(
                $carrier,
                $request->file('file'),
                $request->input('document_type_id')
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
     * Cambiar el estado de un documento por defecto.
     */
    public function toggleDefaultDocument(Request $request, $carrierSlug)
    {
        $carrier = $this->findCarrierBySlug($carrierSlug);
        
        if (!$this->canAccessCarrier($carrier)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to modify documents for this carrier.'
            ], 403);
        }

        $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'media_id' => 'required|exists:media,id',
        ]);

        try {
            $result = $this->carrierDocumentService->toggleDefaultDocument(
                $carrier,
                $request->input('document_type_id'),
                $request->input('media_id')
            );

            Log::info('Estado de documento cambiado', [
                'user_id' => Auth::id(),
                'carrier_id' => $carrier->id,
                'document_type_id' => $request->input('document_type_id'),
                'media_id' => $request->input('media_id'),
                'new_status' => $result['is_default']
            ]);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'is_default' => $result['is_default']
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cambiar estado de documento', [
                'user_id' => Auth::id(),
                'carrier_id' => $carrier->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating document status: ' . $e->getMessage()
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
}