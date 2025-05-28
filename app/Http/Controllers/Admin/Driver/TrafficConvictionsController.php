<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverTrafficConviction;
use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TrafficConvictionsController extends Controller
{
    // Vista para todas las infracciones de tráfico
    public function index(Request $request)
    {
        $query = DriverTrafficConviction::query()
            ->with(['userDriverDetail.user', 'userDriverDetail.carrier']);

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

            // Procesar los archivos subidos vía Livewire
            $files = $request->get('traffic_files');
            $uploadedCount = 0;
            
            Log::info('Procesando archivos en store', [
                'files_data' => $files,
                'conviction_id' => $conviction->id
            ]);
            
            if (!empty($files)) {
                $filesArray = json_decode($files, true);
                
                if (is_array($filesArray)) {
                    foreach ($filesArray as $file) {
                        // Verificamos que el archivo exista
                        if (!empty($file['path'])) {
                            $filePath = $file['path'];
                            $disk = config('filesystems.default', 'local');
                            
                            // Si la ruta no tiene el formato completo con base (cuando viene de StorageServiceProvider)
                            if (strpos($filePath, '/') !== 0 && strpos($filePath, ':\\') !== 1) {
                                Log::info('Ruta de archivo relativa: ' . $filePath);
                            } else {
                                // Si es una ruta absoluta, ajustamos para usar el disco correcto
                                $basePath = storage_path('app/' . $disk . '/');
                                $filePath = str_replace($basePath, '', $filePath);
                                Log::info('Ruta de archivo absoluta convertida a relativa: ' . $filePath);
                            }
                            
                            // Verificar que el archivo exista en el disco temporal
                            if (Storage::disk($disk)->exists($filePath)) {
                                $driverId = $conviction->userDriverDetail->id;
                                
                                try {
                                    $media = $conviction->addMediaFromDisk($filePath, $disk)
                                        ->usingName($file['original_name'] ?? 'document')
                                        ->usingFileName($file['original_name'] ?? 'document')
                                        ->withCustomProperties([
                                            'original_filename' => $file['original_name'] ?? 'document',
                                            'mime_type' => $file['mime_type'] ?? 'application/octet-stream',
                                            'conviction_id' => $conviction->id,
                                            'driver_id' => $driverId,
                                            'size' => $file['size'] ?? 0
                                        ])
                                        ->toMediaCollection('traffic-tickets');
                                    
                                    $uploadedCount++;
                                    
                                    Log::info('Documento de infracción de tráfico subido correctamente', [
                                        'conviction_id' => $conviction->id,
                                        'media_id' => $media->id,
                                        'file_name' => $media->file_name,
                                        'original_name' => $file['original_name'],
                                        'collection' => $media->collection_name,
                                        'driver_id' => $driverId
                                    ]);
                                } catch (\Exception $e) {
                                    Log::error('Error al subir documento de infracción', [
                                        'error' => $e->getMessage(),
                                        'file' => $filePath,
                                        'conviction_id' => $conviction->id
                                    ]);
                                }
                            } else {
                                Log::error('Archivo no encontrado en disco temporal', [
                                    'path' => $filePath,
                                    'disk' => $disk,
                                    'full_path' => storage_path('app/' . $disk . '/' . $filePath)
                                ]);
                            }
                        }
                    }
                } else {
                    Log::error('JSON inválido en traffic_files', ['raw_data' => $files]);
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
                        ->toMediaCollection('traffic-tickets');
                        
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

        $carriers = Carrier::where('status', 1)->get();

        return view('admin.drivers.traffic.edit', compact('conviction', 'drivers', 'carriers'));
    }

    /**
     * Método para actualizar una infracción de tráfico existente
     */
    public function update(DriverTrafficConviction $conviction, Request $request)
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

            $conviction->update($validated);
            
            // Si hay documentos nuevos subidos vía Livewire
            $files = $request->get('traffic_files');
            $uploadedCount = 0;
            
            Log::info('Procesando archivos en update', [
                'files_data' => $files,
                'conviction_id' => $conviction->id
            ]);
            
            if (!empty($files)) {
                try {
                    $filesArray = json_decode($files, true);
                    
                    if (is_array($filesArray)) {
                        foreach ($filesArray as $file) {
                            if (empty($file['path'])) {
                                continue;
                            }
                            
                            // Obtener la ruta completa del archivo
                            $filePath = $file['path'];
                            $fullPath = storage_path('app/' . $filePath);
                            
                            // Verificar si el archivo existe físicamente
                            if (!file_exists($fullPath)) {
                                Log::error('Archivo no encontrado', [
                                    'path' => $filePath,
                                    'full_path' => $fullPath,
                                    'conviction_id' => $conviction->id
                                ]);
                                continue;
                            }
                            
                            $driverId = $conviction->userDriverDetail->id;
                            
                            // Usar addMedia directamente desde la ruta del archivo
                            $media = $conviction->addMedia($fullPath)
                                ->usingName($file['original_name'] ?? 'document')
                                ->usingFileName($file['original_name'] ?? 'document')
                                ->withCustomProperties([
                                    'original_filename' => $file['original_name'] ?? 'document',
                                    'mime_type' => $file['mime_type'] ?? 'application/octet-stream',
                                    'conviction_id' => $conviction->id,
                                    'driver_id' => $driverId,
                                    'size' => $file['size'] ?? 0
                                ])
                                ->toMediaCollection('traffic-tickets');
                            
                            $uploadedCount++;
                            
                            Log::info('Documento subido correctamente en update', [
                                'conviction_id' => $conviction->id,
                                'media_id' => $media->id,
                                'file_name' => $media->file_name,
                                'collection' => 'traffic-tickets'
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error al procesar documentos vía Livewire', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'conviction_id' => $conviction->id
                    ]);
                }
            }
            
            // También manejar archivos subidos directamente vía formulario (no Livewire)
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    // Obtener el ID del conductor asociado a la infracción
                    $driverId = $conviction->userDriverDetail->id;
                    
                    // Configurar el disco y la ruta de almacenamiento correcta
                    $media = $conviction->addMedia($file)
                        ->usingName($file->getClientOriginalName())
                        ->usingFileName($file->getClientOriginalName())
                        ->withCustomProperties([
                            'original_filename' => $file->getClientOriginalName(),
                            'mime_type' => $file->getMimeType(),
                            'conviction_id' => $conviction->id,
                            'driver_id' => $driverId
                        ])
                        ->toMediaCollection('traffic-tickets');
                    
                    $uploadedCount++;
                    
                    Log::info('Documento de infracción de tráfico subido directamente durante actualización', [
                        'conviction_id' => $conviction->id,
                        'media_id' => $media->id,
                        'file_name' => $media->file_name,
                        'collection' => $media->collection_name,
                        'driver_id' => $driverId
                    ]);
                }
            }
            
            if ($uploadedCount > 0) {
                session()->flash('success', "Infracción actualizada y $uploadedCount documentos subidos correctamente");
            }

            // Registrar el éxito en el log
            Log::info('Traffic conviction updated successfully', [
                'conviction_id' => $conviction->id,
                'driver_id' => $conviction->user_driver_detail_id
            ]);

            DB::commit();

            // Redireccionar con mensaje de éxito
            return redirect()->route('admin.traffic.index')
                ->with('success', 'Traffic conviction updated successfully');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating traffic conviction', [
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
     */
    public function showDocuments(DriverTrafficConviction $conviction)
    {
        // Recuperar todos los archivos de media asociados con esta infracción de tráfico
        $documents = \Spatie\MediaLibrary\MediaCollections\Models\Media::where('model_type', DriverTrafficConviction::class)
            ->where('model_id', $conviction->id)
            ->get();

        // Información de depuración
        $debugInfo = [
            'conviction_id' => $conviction->id,
            'user_driver_detail_id' => $conviction->user_driver_detail_id,
            'documents_count' => $documents->count(),
            'collections' => [
                'traffic-tickets' => $conviction->getMedia('traffic-tickets')->count(),
                'traffic_documents' => $conviction->getMedia('traffic_documents')->count(),
                'all_media' => $documents->count(),
            ],
            'media_info' => $documents->map(function ($media) {
                return [
                    'id' => $media->id,
                    'file_name' => $media->file_name,
                    'collection_name' => $media->collection_name,
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                ];
            }),
        ];

        return view('admin.drivers.traffic.documents', compact('conviction', 'documents', 'debugInfo'));
    }

    /**
     * Previsualiza un documento relacionado con infracciones de tráfico.
     * 
     * @param int $documentId ID del documento a previsualizar
     * @return \Illuminate\Http\Response Respuesta con la previsualización o descarga
     */
    public function previewDocument($documentId)
    {
        try {
            $media = Media::findOrFail($documentId);
            
            // Verificar que el documento pertenece a una infracción de tráfico
            if ($media->model_type !== DriverTrafficConviction::class) {
                abort(403, 'No tienes permiso para ver este documento');
            }
            
            // Verificar si el archivo existe físicamente
            if (!file_exists($media->getPath())) {
                throw new \Exception("El archivo '{$media->file_name}' no se encuentra en el servidor");
            }
            
            // Servir archivo según tipo
            $mime = $media->mime_type;
            
            if (Str::contains($mime, 'image')) {
                // Para imágenes, mostrar en el navegador
                return response()->file($media->getPath(), ['Content-Type' => $mime]);
            } else {
                // Para otros tipos, forzar descarga
                return response()->download($media->getPath(), $media->file_name, ['Content-Type' => $mime]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error al previsualizar documento: ' . $e->getMessage(), [
                'document_id' => $documentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            abort(404, 'Documento no encontrado: ' . $e->getMessage());
        }
    }

    /**
     * Almacena documentos para una infracción de tráfico
     * 
     * @param DriverTrafficConviction $conviction La infracción a la que se subirán los documentos
     * @param Request $request Solicitud con los documentos a subir
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje de éxito o error
     */
    public function storeDocuments(DriverTrafficConviction $conviction, Request $request)
    {
        try {
            $request->validate([
                'documents' => 'required|array',
                'documents.*' => 'file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx'
            ]);
            
            $uploadedCount = 0;
            
            foreach ($request->file('documents') as $file) {
                // Obtener el ID del conductor asociado a la infracción
                $driverId = $conviction->userDriverDetail->id;
                
                // Configurar el disco y la ruta de almacenamiento correcta
                $media = $conviction->addMedia($file)
                    ->usingName($file->getClientOriginalName())
                    ->usingFileName($file->getClientOriginalName())
                    ->withCustomProperties([
                        'original_filename' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'conviction_id' => $conviction->id,
                        'driver_id' => $driverId
                    ])
                    ->toMediaCollection('traffic-tickets');
                
                $uploadedCount++;
                
                Log::info('Documento de infracción de tráfico subido correctamente', [
                    'conviction_id' => $conviction->id,
                    'media_id' => $media->id,
                    'file_name' => $media->file_name,
                    'collection' => $media->collection_name,
                    'driver_id' => $driverId
                ]);
            }
            
            return redirect()->back()->with('success', "$uploadedCount documentos subidos correctamente");
            
        } catch (\Exception $e) {
            Log::error('Error al subir documentos de infracción de tráfico', [
                'conviction_id' => $conviction->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Error al subir documentos: ' . $e->getMessage());
        }
    }

    /**
     * Elimina un documento (media) asociado a una infracción de tráfico
     * 
     * @param int $documentId ID del documento a eliminar
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyDocument($documentId)
    {
        try {
            // Iniciar una transacción de base de datos para controlar la operación
            DB::beginTransaction();
            
            // 1. Buscar el media primero
            $media = Media::findOrFail($documentId);
            $fileName = $media->file_name;
            $filePath = $media->getPath();
            
            // 2. Verificar que pertenezca a una infracción de tráfico
            if ($media->model_type !== DriverTrafficConviction::class) {
                DB::rollBack();
                return redirect()->back()->with('error', 'El documento no pertenece a una infracción de tráfico');
            }
            
            // 3. Obtener datos de la infracción antes de cualquier operación
            $convictionId = $media->model_id;
            $conviction = DriverTrafficConviction::findOrFail($convictionId);
            $convictionData = $conviction->getAttributes();
            
            // 4. SOLUCIÓN DEFINITIVA: Romper completamente el vínculo antes de eliminar
            // Esto evita que cualquier evento de Spatie pueda encontrar el modelo padre
            DB::table('media')
                ->where('id', $media->id)
                ->update([
                    'model_type' => 'App\\TempDeletedModel', 
                    'model_id' => 0
                ]);
            
            // 5. Ahora que hemos roto la conexión, eliminamos el archivo físico manualmente
            if (file_exists($filePath)) {
                @unlink($filePath);
                Log::info('Archivo físico eliminado: ' . $filePath);
            }
            
            // 6. Eliminar el registro de media de forma segura (ya no tiene vínculo con la infracción)
            DB::table('media')->where('id', $media->id)->delete();
            Log::info('Registro de media eliminado: ' . $documentId);
            
            // 7. VERIFICACIÓN CRÍTICA: Asegurar que la infracción sigue existiendo
            $convictionStillExists = DriverTrafficConviction::find($convictionId);
            
            if (!$convictionStillExists) {
                // Este caso no debería ocurrir, pero por si acaso, recreamos la infracción
                $newConviction = new DriverTrafficConviction();
                foreach ($convictionData as $key => $value) {
                    $newConviction->$key = $value;
                }
                $newConviction->save(['timestamps' => false]);
                
                Log::error('RECUPERACIÓN DE EMERGENCIA: Se recreó la infracción ' . $convictionId . ' que fue eliminada inesperadamente');
            }
            
            // 8. Confirmar transacción y redireccionar
            DB::commit();
            
            Log::info('Documento eliminado exitosamente sin afectar a la infracción', [
                'media_id' => $documentId,
                'conviction_id' => $convictionId,
                'conviction_exists' => DriverTrafficConviction::find($convictionId) ? true : false
            ]);
            
            return redirect()->route('admin.traffic.edit', $convictionId)
                ->with('success', "Documento {$fileName} eliminado correctamente");
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al eliminar documento', [
                'media_id' => $documentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

}
