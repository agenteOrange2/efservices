<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Services\Admin\TempUploadService;
use App\Models\Admin\Driver\DriverCourse;
use App\Models\Admin\Driver\DriverTrainingSchool;
use App\Models\Admin\Driver\DriverAccident;
use App\Models\Admin\Driver\DriverTrafficConviction;
use App\Models\Admin\Driver\DriverTesting;
use App\Models\Admin\Driver\DriverInspection;

class UploadController extends Controller
{
    private TempUploadService $tempUploadService;
    
    /**
     * Mapeo de tipos de modelos a sus clases correspondientes
     */
    private array $modelMapping = [
        'course' => DriverCourse::class,
        'training_school' => DriverTrainingSchool::class,
        'accident' => DriverAccident::class,
        'traffic' => DriverTrafficConviction::class,
        'testing' => DriverTesting::class,
        'inspection' => DriverInspection::class
    ];
    
    public function __construct(TempUploadService $tempUploadService)
    {
        $this->tempUploadService = $tempUploadService;
    }
    
    /**
     * Sube un archivo temporal al servidor
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        try {
            // Validar la solicitud
            $validated = $request->validate([
                'file' => 'required|file|max:10240', // 10MB max
                'type' => 'required|string'
            ]);
            
            $file = $request->file('file');
            $type = $request->input('type');
            
            // Almacenar el archivo
            $result = $this->tempUploadService->store($file, "temp/{$type}");
            
            // Asegurar que el token se guarde en la sesión correcta
            $token = $result['token'];
            $tempFiles = session('temp_files', []);
            
            // Guardar la información del archivo en la sesión
            $tempFiles[$token] = [
                'disk' => 'public',
                'path' => "temp/{$type}/" . basename($result['url']),
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'created_at' => now()->toDateTimeString(),
            ];
            
            // Guardar en la sesión
            session(['temp_files' => $tempFiles]);
            
            // Registrar información en el log para depuración
            Log::info('Archivo temporal guardado correctamente', [
                'token' => $token,
                'path' => $tempFiles[$token]['path'],
                'session_id' => session()->getId()
            ]);
            
            // Devolver respuesta JSON
            return response()->json($result);
        } catch (\Exception $e) {
            // Log del error
            Log::error('Error en carga temporal API: ' . $e->getMessage());
            
            // Devolver respuesta de error
            return response()->json([
                'error' => 'Error al procesar el archivo: ' . $e->getMessage()
            ], 422);
        }
    }
    
    /**
     * Guarda un documento permanente usando Media Library
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeDocument(Request $request)
    {
        try {
            // Validar la solicitud
            $validated = $request->validate([
                'model_type' => 'required|string|in:' . implode(',', array_keys($this->modelMapping)),
                'model_id' => 'required|integer',
                'collection' => 'required|string',
                'token' => 'required|string',
                'custom_properties' => 'nullable|array'
            ]);
            
            $modelType = $request->input('model_type');
            $modelId = $request->input('model_id');
            $collection = $request->input('collection');
            $token = $request->input('token');
            $customProperties = $request->input('custom_properties', []);
            
            // Verificar que el modelo existe
            $modelClass = $this->modelMapping[$modelType];
            $model = $modelClass::findOrFail($modelId);
            
            // Verificar que el archivo temporal existe
            $tempFiles = session('temp_files', []);
            
            if (!isset($tempFiles[$token])) {
                Log::warning('Token no encontrado en archivos temporales', ['token' => $token]);
                
                // Buscar el archivo en el directorio temporal (fallback)
                $tempDir = storage_path('app/public/temp/' . $modelType);
                $files = scandir($tempDir);
                $recentFiles = [];
                
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..') continue;
                    
                    $filePath = $tempDir . '/' . $file;
                    $fileTime = filemtime($filePath);
                    $recentFiles[$file] = $fileTime;
                }
                
                // Ordenar por más reciente
                arsort($recentFiles);
                
                if (empty($recentFiles)) {
                    return response()->json([
                        'error' => 'No se encontró el archivo temporal'
                    ], 404);
                }
                
                // Tomar el archivo más reciente
                $fileName = key($recentFiles);
                $filePath = $tempDir . '/' . $fileName;
                
                Log::info('Encontrado archivo reciente', [
                    'path' => $filePath,
                    'mtime' => date('Y-m-d H:i:s', $recentFiles[$fileName])
                ]);
            } else {
                $tempFile = $tempFiles[$token];
                $filePath = storage_path('app/public/' . $tempFile['path']);
            }
            
            // Verificar que el archivo existe
            if (!file_exists($filePath)) {
                return response()->json([
                    'error' => 'El archivo temporal no existe físicamente'
                ], 404);
            }
            
            // Usar Spatie Media Library para guardar el documento
            $media = $model->addMedia($filePath)
                ->withCustomProperties($customProperties)
                ->toMediaCollection($collection);
            
            // Limpiar el archivo temporal de la sesión
            if (isset($tempFiles[$token])) {
                unset($tempFiles[$token]);
                session(['temp_files' => $tempFiles]);
            }
            
            // Devolver la información del archivo guardado
            return response()->json([
                'success' => true,
                'message' => 'Documento guardado correctamente',
                'document' => [
                    'id' => $media->id,
                    'name' => $media->name,
                    'file_name' => $media->file_name,
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                    'collection' => $media->collection_name,
                    'url' => $media->getUrl(),
                    'custom_properties' => $media->custom_properties
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al guardar documento permanente', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Error al guardar el documento: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Elimina un documento usando una solución segura para evitar eliminación en cascada
     * 
     * @param int $id ID del documento a eliminar
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteDocument($id)
    {
        try {
            // Buscar el media item
            $media = DB::table('media')->where('id', $id)->first();
            
            if (!$media) {
                return response()->json([
                    'error' => 'Documento no encontrado'
                ], 404);
            }
            
            // IMPORTANTE: Solución para evitar eliminación en cascada
            // En lugar de usar $media->delete() que podría eliminar el modelo al que está asociado,
            // eliminamos directamente el registro de la tabla media
            
            // Primero, eliminar el archivo físico
            $diskName = $media->disk;
            $path = $media->id . '/' . $media->file_name;
            
            // Verificar si el archivo existe antes de intentar eliminarlo
            if (Storage::disk($diskName)->exists($path)) {
                Storage::disk($diskName)->delete($path);
            }
            
            // Luego, eliminar el registro de la base de datos
            DB::table('media')->where('id', $id)->delete();
            
            Log::info('Documento eliminado correctamente', ['media_id' => $id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Documento eliminado correctamente'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar documento', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Error al eliminar el documento: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtiene los documentos asociados a un modelo
     * 
     * @param string $type Tipo de modelo
     * @param int $id ID del modelo
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDocuments($type, $id)
    {
        try {
            if (!array_key_exists($type, $this->modelMapping)) {
                return response()->json([
                    'error' => 'Tipo de modelo no válido'
                ], 400);
            }
            
            $modelClass = $this->modelMapping[$type];
            $model = $modelClass::findOrFail($id);
            
            // Obtener todos los documentos asociados al modelo
            $documents = $model->media->map(function ($media) {
                return [
                    'id' => $media->id,
                    'name' => $media->name,
                    'file_name' => $media->file_name,
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                    'collection' => $media->collection_name,
                    'url' => $media->getUrl(),
                    'custom_properties' => $media->custom_properties,
                    'created_at' => $media->created_at->format('Y-m-d H:i:s')
                ];
            });
            
            return response()->json([
                'success' => true,
                'documents' => $documents
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al obtener documentos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Error al obtener los documentos: ' . $e->getMessage()
            ], 500);
        }
    }
}
