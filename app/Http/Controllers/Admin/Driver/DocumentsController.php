<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\DocumentAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class DocumentsController extends Controller
{
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
