<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverTrafficConviction;
use App\Models\Carrier;
use App\Models\DocumentAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class TrafficConvictionsController extends Controller
{
    // Vista para todas las infracciones de tráfico
    public function index(Request $request)
    {
        $query = DriverTrafficConviction::query()
            ->with(['userDriverDetail.user', 'userDriverDetail.carrier', 'media']);

        // Aplicar filtros
        if ($request->filled('search_term')) {
            $query->where(function ($q) use ($request) {
                $q->where('charge', 'like', '%' . $request->search_term . '%')
                    ->orWhere('location', 'like', '%' . $request->search_term . '%')
                    ->orWhere('penalty', 'like', '%' . $request->search_term . '%');
            });
        }

        if ($request->filled('driver_filter')) {
            $query->where('user_driver_detail_id', $request->driver_filter);
        }

        if ($request->filled('carrier_filter')) {
            $query->whereHas('userDriverDetail', function ($subq) use ($request) {
                $subq->where('carrier_id', $request->carrier_filter);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('conviction_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('conviction_date', '<=', $request->date_to);
        }

        // Ordenar resultados
        $sortField = $request->get('sort_field', 'conviction_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $convictions = $query->paginate(10);
        $drivers = UserDriverDetail::with('user')->get();
        $carriers = Carrier::where('status', 1)->get();

        return view('admin.drivers.traffic.index', compact('convictions', 'drivers', 'carriers'));
    }

    // Vista para el historial de infracciones de tráfico de un conductor específico
    public function driverHistory(UserDriverDetail $driver, Request $request)
    {
        $query = DriverTrafficConviction::where('user_driver_detail_id', $driver->id);

        // Aplicar filtros si existen
        if ($request->filled('search_term')) {
            $query->where('charge', 'like', '%' . $request->search_term . '%')
                ->orWhere('location', 'like', '%' . $request->search_term . '%')
                ->orWhere('penalty', 'like', '%' . $request->search_term . '%');
        }

        // Ordenar resultados
        $sortField = $request->get('sort_field', 'conviction_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $convictions = $query->paginate(10);

        return view('admin.drivers.traffic.driver_history', compact('driver', 'convictions'));
    }

    /**
     * Mostrar el formulario para crear una nueva infracción de tráfico
     */
    public function create()
    {
        // Inicialmente no cargamos conductores, se cargarán vía AJAX cuando se seleccione un carrier
        $drivers = collect(); // Colección vacía
        $carriers = Carrier::where('status', 1)->get();

        return view('admin.drivers.traffic.create', compact('drivers', 'carriers'));
    }

    /**
     * Método para almacenar una nueva infracción de tráfico
     */
    public function store(Request $request)
    {        
        //dd($request->all());
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'user_driver_detail_id' => 'required|exists:user_driver_details,id',
                'conviction_date' => 'required|date',
                'location' => 'required|string|max:255',
                'charge' => 'required|string|max:255',
                'penalty' => 'required|string|max:255',
            ]);

            $conviction = new DriverTrafficConviction($validated);
            $conviction->save();

            // Procesar los archivos subidos vía Livewire usando nuestro nuevo método
            $files = $request->get('traffic_image_files');
            $uploadedCount = 0;
            
            Log::info('Procesando archivos en store usando media library', [
                'conviction_id' => $conviction->id,
                'files_data' => $files ? 'present' : 'empty'
            ]);
            
            if (!empty($files)) {
                try {
                    // Utilizar el nuevo método processLivewireFiles para procesar los archivos
                    $uploadedCount = $this->processLivewireFiles($conviction, $files, 'traffic_images');
                    
                    Log::info('Resultados de proceso de archivos vía Livewire', [
                        'conviction_id' => $conviction->id,
                        'uploaded_count' => $uploadedCount
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error al procesar archivos Livewire en store', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'conviction_id' => $conviction->id
                    ]);
                }
            }
            
            // También manejar archivos subidos directamente vía formulario (no Livewire)
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $driverId = $conviction->userDriverDetail->id;
                    
                    $media = $conviction->addMedia($file)
                        ->usingName($file->getClientOriginalName())
                        ->usingFileName($file->getClientOriginalName())
                        ->withCustomProperties([
                            'original_filename' => $file->getClientOriginalName(),
                            'mime_type' => $file->getMimeType(),
                            'conviction_id' => $conviction->id,
                            'driver_id' => $driverId
                        ])
                        ->toMediaCollection('traffic_images');
                        
                    $uploadedCount++;
                    
                    Log::info('Documento de infracción de tráfico subido directamente durante creación', [
                        'conviction_id' => $conviction->id,
                        'media_id' => $media->id,
                        'file_name' => $media->file_name,
                        'collection' => $media->collection_name
                    ]);
                }
            }

            DB::commit();
            
            Log::info('Traffic conviction created successfully', [
                'conviction_id' => $conviction->id,
                'driver_id' => $conviction->user_driver_detail_id
            ]);

            return redirect()
                ->route('admin.traffic.index')
                ->with('success', 'Traffic conviction created successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating traffic conviction: ' . $e->getMessage(), [
                'error' => $e->getMessage()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Error creating traffic conviction: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar el formulario para editar una infracción de tráfico
     */
    public function edit(DriverTrafficConviction $conviction)
    {
        // Obtenemos el carrier del conductor asociado a la infracción
        $carrierId = $conviction->userDriverDetail->carrier_id;

        // Obtenemos todos los conductores activos del carrier seleccionado
        $drivers = UserDriverDetail::where('carrier_id', $carrierId)
            ->where('status', UserDriverDetail::STATUS_ACTIVE)
            ->with('user')
            ->get();

        // Si el conductor de la infracción no está en la lista (podría estar inactivo),
        // lo añadimos manualmente para que aparezca en el formulario
        $driverExists = $drivers->contains('id', $conviction->user_driver_detail_id);
        if (!$driverExists) {
            $convictionDriver = UserDriverDetail::with('user')->find($conviction->user_driver_detail_id);
            if ($convictionDriver) {
                $drivers->push($convictionDriver);
            }
        }

        // Cargar los archivos de Media Library asociados a esta infracción
        $conviction->load('media');
        
        $carriers = Carrier::where('status', 1)->get();

        return view('admin.drivers.traffic.edit', compact('conviction', 'drivers', 'carriers'));
    }

    /**
     * Método para actualizar una infracción de tráfico existente
     */
    public function update(DriverTrafficConviction $conviction, Request $request)
    {
        DB::beginTransaction();
        
        try {
            // Validar datos de la infracción
            $validated = $request->validate([
                'user_driver_detail_id' => 'required|exists:user_driver_details,id',
                'conviction_date' => 'required|date',
                'location' => 'required|string|max:255',
                'charge' => 'required|string|max:255',
                'penalty' => 'required|string|max:255',
            ]);

            // Actualizar la infracción con los datos validados
            $conviction->update($validated);
            $uploadedCount = 0;
            
            // 1. Procesar archivos subidos por Livewire usando el nuevo método
            $files = $request->get('traffic_image_files');
            
            Log::info('Procesando archivos en update usando media library', [
                'conviction_id' => $conviction->id,
                'files_data' => $files ? 'present' : 'empty'
            ]);
            
            if (!empty($files)) {
                try {
                    // Utilizar el nuevo método processLivewireFiles para procesar los archivos
                    // Usamos 'traffic_images' como nombre de colección para mantener consistencia
                    $uploadedCount = $this->processLivewireFiles($conviction, $files, 'traffic_images');
                    
                    Log::info('Resultados de proceso de archivos vía Livewire en update', [
                        'conviction_id' => $conviction->id,
                        'uploaded_count' => $uploadedCount
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error al procesar archivos Livewire en update', [  
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'conviction_id' => $conviction->id
                    ]);
                }
            }
            
            // 2. Procesar archivos subidos directamente vía formulario
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    // Obtener el ID del conductor
                    $driverId = $conviction->userDriverDetail->id;
                    
                    // Almacenar temporalmente el archivo
                    $tempPath = $file->store('temp');
                    $fullPath = storage_path('app/' . $tempPath);
                    
                    // Propiedades personalizadas para el documento
                    $customProperties = [
                        'conviction_id' => $conviction->id,
                        'driver_id' => $driverId,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize()
                    ];
                    
                    // Usar Media Library directamente para mantener consistencia
                    $media = $conviction->addMedia($fullPath)
                        ->usingName($file->getClientOriginalName())
                        ->withCustomProperties($customProperties)
                        ->toMediaCollection('traffic_images');
                        
                    $uploadedCount++;
                    
                    Log::info('Documento subido directamente en update', [
                        'conviction_id' => $conviction->id,
                        'media_id' => $media->id,
                        'file_name' => $media->file_name,
                    ]);
                }
            }
            
            DB::commit();

            return redirect()->route('admin.traffic.index')
                ->withInput()
                ->with('success', 'Traffic conviction updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar infracción de tráfico: ' . $e->getMessage(), [
                'conviction_id' => $conviction->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating traffic conviction: ' . $e->getMessage());
        }
    }

    // Método para eliminar una infracción de tráfico
    public function destroy(DriverTrafficConviction $conviction)
    {
        $driverId = $conviction->user_driver_detail_id;
        $conviction->delete();

        Session::flash('success', 'Traffic conviction record deleted successfully!');

        // Determinar la ruta de retorno basado en la URL de referencia
        $referer = request()->headers->get('referer');
        if (strpos($referer, 'traffic-history') !== false) {
            return redirect()->route('admin.drivers.traffic-history', $driverId);
        }

        return redirect()->route('admin.traffic.index');
    }

    public function getDriversByCarrier(Carrier $carrier)
    {
        $drivers = UserDriverDetail::where('carrier_id', $carrier->id)
            ->where('status', UserDriverDetail::STATUS_ACTIVE)
            ->with(['user'])
            ->get();

        return response()->json($drivers);
    }

    /**
     * Mostrar los documentos de una infracción de tráfico
     * 
     * @param \App\Models\Admin\Driver\DriverTrafficConviction $conviction
     * @return \Illuminate\View\View
     */
    public function showDocuments(DriverTrafficConviction $conviction)
    {
        // Cargar la relación con el conductor y el transportista
        $conviction->load(['userDriverDetail.user', 'userDriverDetail.carrier']);
        
        // Obtener los documentos de la colección 'traffic_images' usando Spatie Media Library
        $mediaItems = $conviction->getMedia('traffic_images');
        
        // Obtener también los documentos antiguos usando DocumentAttachment para compatibilidad
        $legacyDocuments = \App\Models\DocumentAttachment::where('documentable_type', DriverTrafficConviction::class)
            ->where('documentable_id', $conviction->id)
            ->get();
        
        // Información de depuración
        $debugInfo = [
            'conviction_id' => $conviction->id,
            'user_driver_detail_id' => $conviction->user_driver_detail_id,
            'media_items_count' => $mediaItems->count(),
            'legacy_documents_count' => $legacyDocuments->count(),
            'media_items' => $mediaItems->map(function ($media) {
                return [
                    'id' => $media->id,
                    'file_name' => $media->file_name,
                    'collection_name' => $media->collection_name,
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                    'url' => $media->getUrl(),
                ];
            }),
        ];
        
        Log::info('Mostrando documentos de infracción de tráfico', [
            'conviction_id' => $conviction->id,
            'media_count' => $mediaItems->count(),
            'legacy_count' => $legacyDocuments->count()
        ]);

        return view('admin.drivers.traffic.documents', compact('conviction', 'mediaItems', 'legacyDocuments', 'debugInfo'));
    }

    /**
     * Previsualiza un documento relacionado con infracciones de tráfico.
     * 
     * @param int $documentId ID del documento a previsualizar
        }
        
        // Si es un PDF, abrirlo en el navegador
        if ($mime === 'application/pdf') {
     */
    public function previewDocument($documentId)
    {
        try {
            // Iniciar una transacción de base de datos para controlar la operación
            DB::beginTransaction();
            
            // 1. Buscar el documento
            $document = DocumentAttachment::findOrFail($documentId);
            $fileName = $document->file_name;
            $filePath = $document->getPath();
            $mime = $document->mime_type;
            
            // 2. Verificar que el archivo exista físicamente
            if (!file_exists($filePath)) {
                DB::rollBack();
                return redirect()->back()->with('error', 'El archivo no existe');
            }
            
            // 3. Previsualizar el documento
            if ($mime === 'application/pdf') {
                return response()->file($filePath, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $fileName . '"'
                ]);
            } elseif (in_array($mime, ['image/jpeg', 'image/png', 'image/jpg'])) {
                return response()->file($filePath, [
                    'Content-Type' => $mime,
                    'Content-Disposition' => 'inline; filename="' . $fileName . '"'
                ]);
            } else {
                return response()->download($filePath, $fileName, [
                    'Content-Type' => $mime,
                ]);
            }
            
            // 4. Confirmar transacción
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al previsualizar documento', [
                'document_id' => $documentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Error al previsualizar documento: ' . $e->getMessage());
        }
    }

    /**
     * Elimina un documento asociado a una infracción de tráfico
     * Maneja tanto documentos antiguos (DocumentAttachment) como documentos de Media Library
     * 
     * @param int|string $documentId ID del documento a eliminar
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function destroyDocument($documentId)
    {
        // Verificar si el ID comienza con 'media_' o es numérico pero corresponde a un Media item
        if ((is_string($documentId) && strpos($documentId, 'media_') === 0) || 
            (is_numeric($documentId) && \Spatie\MediaLibrary\MediaCollections\Models\Media::find($documentId))) {
            // Es un documento de Media Library, usar el método deleteDocumentDirectly
            return $this->deleteDocumentDirectly($documentId);
        }
        
        // Es un documento antiguo (DocumentAttachment)
        try {
            // Iniciar una transacción de base de datos para controlar la operación
            DB::beginTransaction();
            
            // 1. Buscar el documento
            $document = DocumentAttachment::findOrFail($documentId);
            $fileName = $document->file_name;
            
            // 2. Verificar que pertenezca a una infracción de tráfico
            if ($document->documentable_type !== DriverTrafficConviction::class) {
                DB::rollBack();
                
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El documento no pertenece a una infracción de tráfico'
                    ], 400);
                }
                
                return redirect()->back()->with('error', 'El documento no pertenece a una infracción de tráfico');
            }
            
            // 3. Obtener la infracción asociada
            $convictionId = $document->documentable_id;
            $conviction = DriverTrafficConviction::findOrFail($convictionId);
            
            // 4. Eliminar el documento usando el método del trait HasDocuments
            $conviction->deleteDocument($documentId);
            
            // 5. Registrar la operación
            Log::info('Documento antiguo eliminado exitosamente', [
                'document_id' => $documentId,
                'conviction_id' => $convictionId,
                'file_name' => $fileName
            ]);
            
            // 6. Confirmar transacción
            DB::commit();
            
            // 7. Devolver respuesta según el tipo de solicitud
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Documento {$fileName} eliminado correctamente",
                    'conviction_id' => $convictionId
                ]);
            }
            
            return redirect()->route('admin.traffic.edit', $convictionId)
                ->with('success', "Documento {$fileName} eliminado correctamente");
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al eliminar documento antiguo', [
                'document_id' => $documentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Elimina un documento mediante una solicitud AJAX
     * 
     * @param int $mediaId El ID del medio a eliminar
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxDestroyDocument($mediaId)
    {
        try {
            Log::info('Solicitud de eliminación de media recibida', [
                'media_id' => $mediaId
            ]);
            
            // Iniciar una transacción de base de datos
            DB::beginTransaction();
            
            // 1. Buscar el registro del medio directamente en la tabla media
            $mediaRecord = DB::table('media')->where('id', $mediaId)->first();
            
            if (!$mediaRecord) {
                Log::warning('Medio no encontrado', ['media_id' => $mediaId]);
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Error: Medio no encontrado'
                ], 404);
            }
            
            // 2. Verificar que pertenezca a una infracción de tráfico
            if ($mediaRecord->model_type !== DriverTrafficConviction::class) {
                Log::warning('El medio no pertenece a una infracción de tráfico', [
                    'media_id' => $mediaId,
                    'model_type' => $mediaRecord->model_type
                ]);
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'El documento no pertenece a una infracción de tráfico'
                ], 400);
            }
            
            // 3. Obtener la infracción asociada
            $convictionId = $mediaRecord->model_id;
            $conviction = DriverTrafficConviction::findOrFail($convictionId);
            
            // 4. Usar el método safeDeleteMedia
            Log::info('Eliminando medio con safeDeleteMedia', [
                'media_id' => $mediaId,
                'conviction_id' => $convictionId
            ]);
            $result = $conviction->safeDeleteMedia($mediaId);
            
            if (!$result) {
                Log::error('Error al eliminar el medio', ['media_id' => $mediaId]);
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar el medio'
                ], 500);
            }
            
            // 5. Confirmar transacción
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Documento eliminado correctamente"
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al eliminar documento vía AJAX', [
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
     * Método privado para procesar archivos subidos vía Livewire
     * 
     * @param DriverTrafficConviction $conviction Infracción a la que asociar los archivos
     * @param string $filesJson Datos de los archivos en formato JSON
     * @param string $collection Nombre de la colección donde guardar los archivos
     * @return int Número de archivos procesados correctamente
     */
    private function processLivewireFiles(DriverTrafficConviction $conviction, $filesJson, $collection)
    {
        $uploadedCount = 0;
        
        try {
            // Si no hay datos de archivos, salir
            if (empty($filesJson)) {
                return 0;
            }
            
            $filesArray = json_decode($filesJson, true);
            Log::info('Procesando archivos para media', ['files' => $filesArray]);
            
            if (is_array($filesArray)) {
                foreach ($filesArray as $file) {
                    // Verificar si es un archivo existente (ya procesado anteriormente)
                    if (isset($file['is_temp']) && $file['is_temp'] === false) {
                        Log::info('Archivo ya procesado, no requiere acción', ['file' => $file]);
                        continue;
                    }
                    
                    // Verificar si tenemos la ruta del archivo
                    $filePath = null;
                    if (!empty($file['path'])) {
                        $filePath = $file['path'];
                    } elseif (!empty($file['tempPath'])) {
                        $filePath = $file['tempPath'];
                    } else {
                        Log::warning('Archivo sin ruta temporal', ['file' => $file]);
                        continue;
                    }
                    
                    // Verificar si el archivo existe físicamente
                    $fullPath = storage_path('app/' . $filePath);
                    if (!file_exists($fullPath)) {
                        // Intentar buscar en la carpeta temp directamente
                        $filePath = 'temp/' . basename($filePath);
                        $fullPath = storage_path('app/' . $filePath);
                        
                        if (!file_exists($fullPath)) {
                            Log::error('Archivo no encontrado', [
                                'path' => $filePath,
                                'full_path' => $fullPath,
                                'conviction_id' => $conviction->id
                            ]);
                            continue;
                        }
                    }
                    
                    // Obtener el nombre del archivo y otros metadatos
                    $fileName = $file['name'] ?? $file['original_name'] ?? basename($fullPath);
                    $mimeType = $file['mime_type'] ?? mime_content_type($fullPath);
                    $fileSize = $file['size'] ?? filesize($fullPath);
                    
                    try {
                        // Usar DIRECTAMENTE el sistema de medios de Spatie
                        $media = $conviction->addMedia($fullPath)
                            ->usingName($fileName)
                            ->withCustomProperties([
                                'driver_id' => $conviction->user_driver_detail_id,
                                'conviction_id' => $conviction->id,
                                'document_type' => 'traffic_image'
                            ])
                            ->toMediaCollection($collection);
                        
                        $uploadedCount++;
                        
                        Log::info('Imagen guardada correctamente en media', [
                            'conviction_id' => $conviction->id,
                            'file_name' => $fileName,
                            'collection' => $collection,
                            'media_id' => $media->id
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error al guardar la imagen en media', [
                            'error' => $e->getMessage(),
                            'file' => $fileName,
                            'conviction_id' => $conviction->id
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error al procesar imágenes de infracción', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'conviction_id' => $conviction->id,
                'collection' => $collection
            ]);
        }
        
        return $uploadedCount;
    }
    
    /**
     * Elimina un documento de infracción de tráfico de manera segura
     * Usa eliminación directa de la tabla media para evitar eliminación en cascada
     * 
     * @param int $mediaId ID del documento en la tabla media
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function deleteDocumentDirectly($mediaId)
    {
        try {
            // Extraer el ID numérico si viene en formato 'media_XXX'
            if (is_string($mediaId) && strpos($mediaId, 'media_') === 0) {
                $mediaId = (int) substr($mediaId, 6); // Extraer el número después de 'media_'
            }
            
            // Registrar inicio de la operación para depuración
            Log::info('Iniciando eliminación de documento de infracción de tráfico', [
                'media_id' => $mediaId,
                'user_id' => Auth::id()
            ]);
            
            // Buscar el archivo en la tabla media para obtener información del archivo físico
            $media = DB::table('media')
                ->select('media.*', 'driver_traffic_convictions.user_driver_detail_id', 'driver_traffic_convictions.id as conviction_id')
                ->join('driver_traffic_convictions', 'media.model_id', '=', 'driver_traffic_convictions.id')
                ->where('media.id', $mediaId)
                ->where('media.model_type', 'App\\Models\\Admin\\Driver\\DriverTrafficConviction')
                ->first();
            
            if (!$media) {
                Log::warning('Documento no encontrado para eliminar', ['media_id' => $mediaId]);
                
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json(['error' => 'Documento no encontrado'], 404);
                }
                
                return redirect()->back()->with('error', 'Documento no encontrado');
            }
            
            // Registrar información del documento para depuración
            Log::info('Información del documento a eliminar', [
                'media_id' => $mediaId,
                'file_name' => $media->file_name ?? 'unknown',
                'conviction_id' => $media->conviction_id ?? 'unknown',
                'driver_id' => $media->user_driver_detail_id ?? 'unknown'
            ]);
            
            // Lista de posibles rutas donde podría estar el archivo físico
            $possiblePaths = [
                // Ruta estándar de Media Library
                storage_path('app/public/' . $media->id . '/' . $media->file_name),
                storage_path('app/' . $media->id . '/' . $media->file_name),
                
                // Rutas basadas en la estructura de carpetas personalizada
                storage_path('app/public/driver/' . $media->user_driver_detail_id . '/traffic/' . $media->conviction_id . '/' . $media->file_name),
                storage_path('app/public/' . $media->conviction_id . '/traffic_images/' . $media->file_name),
                storage_path('app/public/traffic_images/' . $media->conviction_id . '/' . $media->file_name),
                
                // Rutas adicionales basadas en posibles convenciones
                storage_path('app/public/' . $media->model_id . '/' . $media->file_name),
                storage_path('app/public/traffic_images/' . $media->file_name),
                storage_path('app/public/driver/' . $media->user_driver_detail_id . '/traffic_convictions/' . $media->file_name)
            ];
            
            // Variable para rastrear si se eliminó algún archivo
            $fileDeleted = false;
            
            // Intentar eliminar el archivo en todas las posibles ubicaciones
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    Log::info('Archivo físico encontrado', ['path' => $path]);
                    
                    try {
                        unlink($path);
                        $fileDeleted = true;
                        Log::info('Archivo físico eliminado correctamente', ['path' => $path]);
                    } catch (\Exception $e) {
                        Log::warning('Error al eliminar archivo físico', [
                            'path' => $path,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
            
            if (!$fileDeleted) {
                Log::warning('No se encontró el archivo físico para eliminar', [
                    'media_id' => $mediaId,
                    'file_name' => $media->file_name ?? 'unknown'
                ]);
            }
            
            // Eliminar directamente de la tabla media para evitar eliminación en cascada
            // NO usar $media->delete() ya que esto podría causar problemas con relaciones
            $deleted = DB::table('media')->where('id', $mediaId)->delete();
            
            if ($deleted) {
                // Registrar la eliminación exitosa
                Log::info('Documento de infracción de tráfico eliminado correctamente', [
                    'media_id' => $mediaId,
                    'user_id' => Auth::id(),
                    'conviction_id' => $media->conviction_id ?? null
                ]);
                
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Documento eliminado correctamente',
                        'media_id' => $mediaId
                    ]);
                }
                
                // Determinar la URL de retorno basada en la referencia
                $referer = request()->headers->get('referer');
                return redirect($referer ?: route('admin.traffic.index'))
                    ->with('success', 'Documento eliminado correctamente');
            } else {
                Log::warning('No se pudo eliminar el documento de la base de datos', ['media_id' => $mediaId]);
                
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'error' => 'No se pudo eliminar el documento de la base de datos'
                    ], 500);
                }
                
                return redirect()->back()->with('error', 'No se pudo eliminar el documento');
            }
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar documento de infracción de tráfico', [
                'media_id' => $mediaId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'error' => 'Error al eliminar documento: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Error al eliminar documento: ' . $e->getMessage());
        }
    }
    

}
