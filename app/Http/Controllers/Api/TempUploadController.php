<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TempDriverUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Exception;

class TempUploadController extends Controller
{
    /**
     * Upload license file temporarily before driver registration
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadLicense(Request $request)
    {
        try {
            // Validar la solicitud
            $validator = Validator::make($request->all(), [
                'file' => [
                    'required',
                    'file',
                    'mimes:jpeg,jpg,png,pdf',
                    'max:5120', // 5MB max
                    'dimensions:min_width=800,min_height=600'
                ],
                'type' => 'required|in:license_front,license_back',
                'session_id' => 'required|string|min:10|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $sessionId = $request->session_id;
            $fileType = $request->type;
            $file = $request->file('file');

            Log::info('TempUploadController: Iniciando carga temporal', [
                'session_id' => $sessionId,
                'file_type' => $fileType,
                'original_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType()
            ]);

            // Verificar si ya existe un archivo del mismo tipo para esta sesión
            $existingUpload = TempDriverUpload::where('session_id', $sessionId)
                ->where('file_type', $fileType)
                ->first();

            if ($existingUpload) {
                // Eliminar el archivo anterior y su media
                $existingUpload->clearMediaCollection($fileType);
                $existingUpload->delete();
                
                Log::info('TempUploadController: Archivo anterior eliminado', [
                    'session_id' => $sessionId,
                    'file_type' => $fileType,
                    'previous_upload_id' => $existingUpload->id
                ]);
            }

            // Crear modelo temporal
            $tempUpload = TempDriverUpload::create([
                'session_id' => $sessionId,
                'file_type' => $fileType,
                'original_name' => $file->getClientOriginalName(),
                'expires_at' => now()->addHours(24)
            ]);

            // Obtener dimensiones de la imagen si es una imagen
            $dimensions = null;
            if (in_array($file->getMimeType(), ['image/jpeg', 'image/jpg', 'image/png'])) {
                $imageSize = getimagesize($file->getPathname());
                if ($imageSize) {
                    $dimensions = $imageSize[0] . 'x' . $imageSize[1];
                }
            }

            // Subir archivo usando Spatie Media Library
            $mediaAdder = $tempUpload->addMediaFromRequest('file')
                ->usingName($file->getClientOriginalName())
                ->usingFileName(Str::uuid() . '.' . $file->getClientOriginalExtension());

            // Agregar dimensiones como custom property si están disponibles
            if ($dimensions) {
                $mediaAdder->withCustomProperties(['dimensions' => $dimensions]);
            }

            $media = $mediaAdder->toMediaCollection($fileType);

            Log::info('TempUploadController: Archivo subido exitosamente', [
                'temp_upload_id' => $tempUpload->id,
                'media_id' => $media->id,
                'file_path' => $media->getPath(),
                'file_url' => $media->getUrl()
            ]);

            // Preparar información del archivo
            $fileInfo = [
                'size' => $media->size,
                'mime_type' => $media->mime_type,
                'file_name' => $media->file_name,
                'original_name' => $tempUpload->original_name,
                'url' => $media->getUrl()
            ];

            // Agregar URLs de conversiones si están disponibles
            if ($media->hasGeneratedConversion('preview')) {
                $fileInfo['preview_url'] = $media->getUrl('preview');
            }
            
            if ($media->hasGeneratedConversion('thumb')) {
                $fileInfo['thumbnail_url'] = $media->getUrl('thumb');
            }

            if ($dimensions) {
                $fileInfo['dimensions'] = $dimensions;
            }

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'temp_id' => $tempUpload->id,
                'media_id' => $media->id,
                'preview_url' => $media->getUrl(),
                'file_info' => $fileInfo
            ], 200);

        } catch (Exception $e) {
            Log::error('TempUploadController: Error en carga temporal', [
                'session_id' => $request->session_id ?? 'unknown',
                'file_type' => $request->type ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Upload failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get preview of uploaded temporary license
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function previewLicense($id)
    {
        try {
            $tempUpload = TempDriverUpload::findOrFail($id);

            if ($tempUpload->isExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'File has expired'
                ], 410);
            }

            $fileInfo = $tempUpload->getFileInfo();

            if (empty($fileInfo)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'file_info' => $fileInfo
            ]);

        } catch (Exception $e) {
            Log::error('TempUploadController: Error en preview', [
                'temp_upload_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Preview failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete temporary license upload
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteTempLicense($id)
    {
        try {
            $tempUpload = TempDriverUpload::findOrFail($id);

            // Eliminar archivos de media asociados
            $tempUpload->clearMediaCollection($tempUpload->file_type);
            
            // Eliminar registro temporal
            $tempUpload->delete();

            Log::info('TempUploadController: Archivo temporal eliminado', [
                'temp_upload_id' => $id,
                'session_id' => $tempUpload->session_id,
                'file_type' => $tempUpload->file_type
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully'
            ]);

        } catch (Exception $e) {
            Log::error('TempUploadController: Error eliminando archivo temporal', [
                'temp_upload_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Delete failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate license file content (optional OCR validation)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateLicense(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'temp_id' => 'required|integer|exists:temp_driver_uploads,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $tempUpload = TempDriverUpload::findOrFail($request->temp_id);

            if ($tempUpload->isExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'File has expired'
                ], 410);
            }

            // Aquí se puede implementar validación OCR o análisis de contenido
            // Por ahora, validación básica
            $errors = [];
            $suggestions = [];
            $valid = true;

            $media = $tempUpload->getFirstMedia($tempUpload->file_type);
            if (!$media) {
                $errors[] = 'File not found';
                $valid = false;
            } else {
                // Validaciones básicas
                if ($media->size > 5120 * 1024) { // 5MB
                    $errors[] = 'File size too large';
                    $valid = false;
                }

                if (!in_array($media->mime_type, ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'])) {
                    $errors[] = 'Invalid file format';
                    $valid = false;
                }

                // Sugerencias de mejora
                if ($media->size < 100 * 1024) { // 100KB
                    $suggestions[] = 'Consider uploading a higher quality image';
                }
            }

            return response()->json([
                'success' => true,
                'valid' => $valid,
                'errors' => $errors,
                'suggestions' => $suggestions
            ]);

        } catch (Exception $e) {
            Log::error('TempUploadController: Error en validación', [
                'temp_id' => $request->temp_id ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all temporary uploads for a session
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSessionUploads(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'session_id' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $uploads = TempDriverUpload::bySession($request->session_id)
                ->notExpired()
                ->with('media')
                ->get();

            $uploadData = $uploads->map(function ($upload) {
                return [
                    'id' => $upload->id,
                    'file_type' => $upload->file_type,
                    'original_name' => $upload->original_name,
                    'expires_at' => $upload->expires_at,
                    'file_info' => $upload->getFileInfo()
                ];
            });

            return response()->json([
                'success' => true,
                'uploads' => $uploadData
            ]);

        } catch (Exception $e) {
            Log::error('TempUploadController: Error obteniendo uploads de sesión', [
                'session_id' => $request->session_id ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get session uploads',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}