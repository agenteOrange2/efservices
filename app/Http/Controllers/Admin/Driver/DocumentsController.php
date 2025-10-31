<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\DocumentAttachment;
use App\Models\UserDriverDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class DocumentsController extends Controller
{
    /**
     * Store a new document for a driver
     * 
     * @param Request $request
     * @param int $driverId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $driverId)
    {
        try {
            $driver = UserDriverDetail::findOrFail($driverId);
            
            $request->validate([
                'name' => 'required|string|max:255',
                'category' => 'required|string',
                'document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
                'type' => 'nullable|string',
                'expires_at' => 'nullable|date',
                'description' => 'nullable|string'
            ]);
            
            $driver->addMediaFromRequest('document')
                   ->withCustomProperties([
                       'name' => $request->name,
                       'category' => $request->category,
                       'type' => $request->type,
                       'expires_at' => $request->expires_at,
                       'description' => $request->description
                   ])
                   ->toMediaCollection('documents');
            
            return redirect()->back()->with('success', 'Document uploaded successfully.');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error uploading document: ' . $e->getMessage());
        }
    }

    /**
     * Elimina un documento por su ID
     * 
     * @param int $documentId ID del documento a eliminar
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($documentId)
    {
        try {
            // Iniciar una transacción de base de datos
            DB::beginTransaction();
            
            // 1. Buscar el documento
            $document = DocumentAttachment::findOrFail($documentId);
            $fileName = $document->file_name;
            
            // 2. Obtener el modelo asociado (documentable)
            $documentableType = $document->documentable_type;
            $documentableId = $document->documentable_id;
            
            // 3. Registrar información para depuración
            Log::info('Solicitud de eliminación de documento via API', [
                'document_id' => $documentId,
                'documentable_type' => $documentableType,
                'documentable_id' => $documentableId,
                'file_name' => $fileName
            ]);
            
            // 4. Obtener la instancia del modelo documentable
            $documentable = $documentableType::findOrFail($documentableId);
            
            // 5. Eliminar el documento usando el método del trait HasDocuments
            $result = $documentable->deleteDocument($documentId);
            
            // 6. Confirmar transacción
            DB::commit();
            
            // 7. Registrar resultado
            Log::info('Documento eliminado vía API', [
                'document_id' => $documentId,
                'resultado' => $result ? 'Exitoso' : 'Fallido'
            ]);
            
            // 8. Responder
            return Response::json([
                'success' => true,
                'message' => "Documento eliminado correctamente"
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al eliminar documento vía API', [
                'document_id' => $documentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Response::json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
