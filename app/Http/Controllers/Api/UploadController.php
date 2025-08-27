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
use App\Models\Admin\Driver\DriverLicense;
use App\Models\Admin\Driver\DriverMedicalQualification;
use App\Models\UserDriverDetail;

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
        'inspection' => DriverInspection::class,
        'user_driver' => UserDriverDetail::class,
        'medical_card' => DriverMedicalQualification::class
    ];
    
    public function __construct(TempUploadService $tempUploadService)
    {
        $this->tempUploadService = $tempUploadService;
    }
    
    /**
     * Sube un archivo directamente a la carpeta específica de licencias
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadLicenseDirect(Request $request)
    {
        try {
            // Validar la solicitud
            $validated = $request->validate([
                'file' => 'required|file|max:10240', // 10MB max
                'type' => 'required|string|in:license_front,license_back',
                'driver_id' => 'required|integer',
                'unique_id' => 'required|string'
            ]);
            
            $file = $request->file('file');
            $type = $request->input('type');
            $driverId = $request->input('driver_id');
            $uniqueId = $request->input('unique_id');
            
            // Verificar que el driver existe
            $driver = UserDriverDetail::findOrFail($driverId);
            
            // Extraer el ID real de la licencia del unique_id (formato: license_123_abc123)
            $licenseId = null;
            if (preg_match('/^license_(\d+)_/', $uniqueId, $matches)) {
                $licenseId = (int)$matches[1];
            }
            
            if (!$licenseId) {
                return response()->json([
                    'error' => 'Formato de unique_id inválido. Se esperaba formato: license_ID_hash. Para licencias nuevas, use el endpoint temporal /api/driver/upload-license-temp',
                    'details' => [
                        'received_unique_id' => $uniqueId,
                        'expected_format' => 'license_[ID]_[hash]',
                        'suggestion' => 'Para licencias nuevas sin ID en base de datos, use el endpoint temporal'
                    ]
                ], 400);
            }
            
            // Buscar la licencia específica usando el ID extraído
            $license = DriverLicense::where('user_driver_detail_id', $driverId)
                ->where('id', $licenseId)
                ->first();
                
            if (!$license) {
                return response()->json([
                    'error' => 'Licencia no encontrada con el ID proporcionado'
                ], 404);
            }
            
            // Determinar la colección y custom_properties basándose en el tipo
            $collection = $type === 'license_front' ? 'license_front' : 'license_back';
            $customProperties = [
                'license_type' => $type === 'license_front' ? 'front' : 'back'
            ];
            
            // Generar nombre único de archivo usando unique_id
            $extension = $file->getClientOriginalExtension();
            $uniqueFileName = $type === 'license_front' 
                ? "card_front_{$uniqueId}.{$extension}"
                : "card_back_{$uniqueId}.{$extension}";
            
            // Guardar directamente usando Media Library en el modelo DriverLicense específico
            $media = $license->addMedia($file)
                ->usingName($file->getClientOriginalName())
                ->usingFileName($uniqueFileName)
                ->withCustomProperties($customProperties)
                ->toMediaCollection($collection);
            
            Log::info('Archivo de licencia guardado directamente', [
                'driver_id' => $driverId,
                'type' => $type,
                'collection' => $collection,
                'media_id' => $media->id,
                'path' => $media->getPath()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Archivo guardado correctamente',
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
            Log::error('Error en upload directo de licencia: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Error al procesar el archivo: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Sube un archivo temporal al servidor (método original mantenido para compatibilidad)
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
            
            // Obtener session ID actual
            $currentSessionId = session()->getId();
            
            // Almacenar el archivo directamente en temp (sin subdirectorio)
            $result = $this->tempUploadService->store($file, "temp");
            
            // Asegurar que el token se guarde en la sesión correcta
            $token = $result['token'];
            $tempFiles = session('temp_files', []);
            
            // Guardar la información del archivo en la sesión con session_id para validación
            $tempFiles[$token] = [
                'disk' => 'public',
                'path' => "temp/" . basename($result['url']),
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'created_at' => now()->toDateTimeString(),
                'session_id' => $currentSessionId, // Guardar session_id con el archivo
                'file_path' => storage_path('app/public/temp/' . basename($result['url'])) // Ruta completa para fallback
            ];
            
            // Guardar en la sesión y forzar persistencia
            session(['temp_files' => $tempFiles]);
            session()->save();
            
            // También guardar en cache como backup
            cache()->put("temp_file_{$token}", $tempFiles[$token], now()->addMinutes(30));
            
            // Registrar información en el log para depuración
            Log::info('Archivo temporal guardado correctamente', [
                'token' => $token,
                'path' => $tempFiles[$token]['path'],
                'session_id' => $currentSessionId,
                'temp_files_count' => count($tempFiles)
            ]);
            
            // Devolver respuesta JSON con session_id para validación
            $result['session_id'] = $currentSessionId;
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
            $currentSessionId = session()->getId();
            
            Log::info('Buscando token en sesión', [
                'token' => $token,
                'session_id' => $currentSessionId,
                'temp_files_count' => count($tempFiles),
                'available_tokens' => array_keys($tempFiles)
            ]);
            
            $tempFile = null;
            $filePath = null;
            
            // Primero intentar obtener de la sesión
            if (isset($tempFiles[$token])) {
                $tempFile = $tempFiles[$token];
                
                // Verificar si la sesión cambió
                if (isset($tempFile['session_id']) && $tempFile['session_id'] !== $currentSessionId) {
                    Log::warning('Sesión cambió entre upload y store', [
                        'token' => $token,
                        'upload_session' => $tempFile['session_id'],
                        'current_session' => $currentSessionId
                    ]);
                }
                
                $filePath = storage_path('app/' . $tempFile['path']);
            }
            
            // Si no se encontró en sesión, intentar cache
            if (!$tempFile) {
                $tempFile = cache()->get("temp_file_{$token}");
                if ($tempFile) {
                    Log::info('Token encontrado en cache', [
                        'token' => $token,
                        'session_id' => $currentSessionId
                    ]);
                    $filePath = $tempFile['file_path'] ?? storage_path('app/' . $tempFile['path']);
                }
            }
            
            // Si aún no se encontró, usar fallback de directorio
            if (!$tempFile) {
                Log::warning('Token no encontrado en sesión ni cache', [
                    'token' => $token,
                    'session_id' => $currentSessionId
                ]);
                
                // Buscar el archivo en el directorio temporal (fallback mejorado)
                $tempDir = storage_path('app/public/temp');
                if (!is_dir($tempDir)) {
                    $tempDir = storage_path('app/temp');
                }
                
                if (!is_dir($tempDir)) {
                    return response()->json([
                        'error' => 'Directorio temporal no encontrado'
                    ], 404);
                }
                
                $files = scandir($tempDir);
                $recentFiles = [];
                
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..' || $file === '.gitignore') continue;
                    
                    $filePath = $tempDir . '/' . $file;
                    // Solo considerar archivos, no directorios
                    if (is_file($filePath)) {
                        $fileTime = filemtime($filePath);
                        // Solo archivos de los últimos 10 minutos
                        if (time() - $fileTime < 600) {
                            $recentFiles[$file] = $fileTime;
                        }
                    }
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
                
                Log::info('Encontrado archivo reciente como fallback', [
                    'path' => $filePath,
                    'mtime' => date('Y-m-d H:i:s', $recentFiles[$fileName])
                ]);
            }
            
            // Verificar que el archivo existe
            if (!file_exists($filePath)) {
                return response()->json([
                    'error' => 'El archivo temporal no existe físicamente'
                ], 404);
            }
            
            // Obtener el nombre original del archivo
            $originalName = $tempFile['original_name'] ?? basename($filePath);
            
            // Preparar custom_properties basándose en el tipo de documento
            $finalCustomProperties = $customProperties;
            
            // Determinar el tipo de licencia basándose en el tipo de documento
            if (isset($tempFile['type'])) {
                if ($tempFile['type'] === 'license_front') {
                    $finalCustomProperties['license_type'] = 'front';
                    Log::info('Configurando custom_properties para license_front', [
                        'token' => $token,
                        'type' => $tempFile['type']
                    ]);
                } elseif ($tempFile['type'] === 'license_back') {
                    $finalCustomProperties['license_type'] = 'back';
                    Log::info('Configurando custom_properties para license_back', [
                        'token' => $token,
                        'type' => $tempFile['type']
                    ]);
                }
            }
            
            // Usar Spatie Media Library para guardar el documento
            $mediaBuilder = $model->addMedia($filePath)
                ->usingName($originalName);
                
            // Agregar custom_properties si existen
            if (!empty($finalCustomProperties)) {
                $mediaBuilder->withCustomProperties($finalCustomProperties);
            }
            
            $media = $mediaBuilder->toMediaCollection($collection);
            
            // Limpiar el archivo temporal de la sesión y cache
            if (isset($tempFiles[$token])) {
                unset($tempFiles[$token]);
                session(['temp_files' => $tempFiles]);
                session()->save();
            }
            
            // Limpiar también del cache
            cache()->forget("temp_file_{$token}");
            
            Log::info('Archivo procesado y limpiado', [
                'token' => $token,
                'final_path' => $media->getPath(),
                'session_id' => session()->getId()
            ]);
            
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

    /**
     * Delete media from a model's collection
     */
    public function deleteMedia(Request $request)
    {
        try {
            $request->validate([
                'model_type' => 'required|string',
                'model_id' => 'required|integer',
                'collection' => 'required|string',
            ]);

            // Check if model type is valid
            if (!array_key_exists($request->model_type, $this->modelMapping)) {
                return response()->json([
                    'error' => 'Invalid model type'
                ], 400);
            }

            // Get the model
            $modelClass = $this->modelMapping[$request->model_type];
            $model = $modelClass::find($request->model_id);

            if (!$model) {
                return response()->json(['error' => 'Model not found'], 404);
            }

            // Delete all media from the specified collection
            $media = $model->getMedia($request->collection);
            foreach ($media as $mediaItem) {
                $mediaItem->delete();
            }

            Log::info('Media deleted successfully', [
                'model_type' => $request->model_type,
                'model_id' => $request->model_id,
                'collection' => $request->collection,
                'deleted_count' => $media->count()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Media deleted successfully',
                'deleted_count' => $media->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Media deletion error', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'error' => 'Failed to delete media: ' . $e->getMessage()
            ], 500);
        }
    }
}
