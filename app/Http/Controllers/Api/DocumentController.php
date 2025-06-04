<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DocumentController extends Controller
{
    /**
     * Elimina un archivo de manera segura sin eliminar el modelo relacionado (método DELETE)
     * 
     * @param int $mediaId
     * @return \Illuminate\Http\JsonResponse
     */
    public function safeDelete($mediaId)
    {
        try {
            // Primero buscar el media para obtener datos del archivo
            $media = Media::find($mediaId);
            
            if (!$media) {
                return response()->json([
                    'success' => false,
                    'message' => 'Media not found'
                ], 404);
            }
            
            // Registrar qué estamos eliminando
            Log::info('Eliminando media de forma segura', [
                'media_id' => $mediaId,
                'file_name' => $media->file_name,
                'model_type' => $media->model_type,
                'model_id' => $media->model_id
            ]);
            
            // Obtener la ruta del archivo para eliminarlo después
            $filePath = $media->getPath();
            
            // Eliminación segura del registro en la tabla media (sin usar el método delete() de Media)
            $deleted = DB::table('media')->where('id', $mediaId)->delete();
            
            if ($deleted) {
                // Eliminar el archivo físico si existe
                if (file_exists($filePath)) {
                    unlink($filePath);
                    
                    // Intentar eliminar el directorio si está vacío
                    $directory = dirname($filePath);
                    if (is_dir($directory) && count(glob("$directory/*")) === 0) {
                        rmdir($directory);
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Document deleted successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar documento de forma segura', [
                'media_id' => $mediaId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Elimina un archivo de manera segura sin eliminar el modelo relacionado (método POST)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function safeDeletePost(Request $request)
    {
        try {
            // Validar que se ha proporcionado el ID del media
            $request->validate([
                'mediaId' => 'required|numeric'
            ]);
            
            $mediaId = $request->mediaId;
            
            // Primero buscar el media para obtener datos del archivo
            $media = Media::find($mediaId);
            
            if (!$media) {
                return response()->json([
                    'success' => false,
                    'message' => 'Media not found'
                ], 404);
            }
            
            // Registrar qué estamos eliminando
            Log::info('Eliminando media de forma segura (POST)', [
                'media_id' => $mediaId,
                'file_name' => $media->file_name,
                'model_type' => $media->model_type,
                'model_id' => $media->model_id
            ]);
            
            // Obtener la ruta del archivo para eliminarlo después
            $filePath = $media->getPath();
            
            // Eliminación segura del registro en la tabla media (sin usar el método delete() de Media)
            // Esto evita la eliminación en cascada del modelo relacionado
            $deleted = DB::table('media')->where('id', $mediaId)->delete();
            
            if ($deleted) {
                // Eliminar el archivo físico si existe
                if (file_exists($filePath)) {
                    unlink($filePath);
                    
                    // Intentar eliminar el directorio si está vacío
                    $directory = dirname($filePath);
                    if (is_dir($directory) && count(glob("$directory/*")) === 0) {
                        rmdir($directory);
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Document deleted successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar documento de forma segura (POST)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
