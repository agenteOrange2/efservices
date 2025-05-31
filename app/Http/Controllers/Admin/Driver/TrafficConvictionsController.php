<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverTrafficConviction;
use App\Models\Carrier;
use App\Models\DocumentAttachment;
use Illuminate\Http\Request;
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
                                    $tempPath = Storage::disk($disk)->path($filePath);
                                    $customProperties = [
                                        'conviction_id' => $conviction->id,
                                        'driver_id' => $driverId,
                                        'original_name' => $file['original_name'] ?? 'document',
                                        'mime_type' => $file['mime_type'] ?? 'application/octet-stream',
                                        'size' => $file['size'] ?? 0
                                    ];
                                    
                                    $document = $conviction->addDocument($tempPath, 'traffic_convictions', $customProperties);
                                    $uploadedCount++;
                                    
                                    Log::info('Documento de infracción de tráfico subido correctamente', [
                                        'conviction_id' => $conviction->id,
                                        'document_id' => $document->id,
                                        'file_name' => $document->file_name,
                                        'original_name' => $file['original_name'],
                                        'collection' => $document->collection,
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
            
            // 1. Procesar archivos subidos por Livewire
            $files = $request->get('traffic_files');
            
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
                            
                            // Usar addDocument del trait HasDocuments
                            $customProperties = [
                                'conviction_id' => $conviction->id,
                                'driver_id' => $driverId,
                                'original_name' => $file['original_name'] ?? 'document',
                                'mime_type' => $file['mime_type'] ?? 'application/octet-stream',
                                'size' => $file['size'] ?? 0
                            ];
                            
                            $document = $conviction->addDocument($fullPath, 'traffic_convictions', $customProperties);
                            $uploadedCount++;
                            
                            Log::info('Documento subido correctamente en update', [
                                'conviction_id' => $conviction->id,
                                'document_id' => $document->id,
                                'file_name' => $document->file_name,
                                'collection' => 'traffic_convictions'
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
                    
                    // Usar addDocument del trait HasDocuments
                    $document = $conviction->addDocument($fullPath, 'traffic_convictions', $customProperties);
                    $uploadedCount++;
                    
                    Log::info('Documento subido directamente en update', [
                        'conviction_id' => $conviction->id,
                        'document_id' => $document->id,
                        'file_name' => $document->file_name,
                        'collection' => 'traffic_convictions'
                    ]);
                }
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
        // Recuperar todos los documentos asociados con esta infracción de tráfico usando DocumentAttachment
        $documents = \App\Models\DocumentAttachment::where('documentable_type', DriverTrafficConviction::class)
            ->where('documentable_id', $conviction->id)
            ->get();

        // Información de depuración
        $debugInfo = [
            'conviction_id' => $conviction->id,
            'user_driver_detail_id' => $conviction->user_driver_detail_id,
            'documents_count' => $documents->count(),
            'collections' => [
                'traffic-tickets' => $documents->where('collection_name', 'traffic-tickets')->count(),
                'traffic_documents' => $documents->where('collection_name', 'traffic_documents')->count(),
                'all_documents' => $documents->count(),
            ],
            'document_info' => $documents->map(function ($document) {
                return [
                    'id' => $document->id,
                    'file_name' => $document->file_name,
                    'collection_name' => $document->collection_name,
                    'mime_type' => $document->mime_type,
                    'size' => $document->size,
                ];
            }),
        ];

        return view('admin.drivers.traffic.documents', compact('conviction', 'documents', 'debugInfo'));
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
     * 
     * @param int $documentId ID del documento a eliminar
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function destroyDocument($documentId)
    {
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
            Log::info('Documento eliminado exitosamente', [
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
            
            Log::error('Error al eliminar documento', [
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
     * @param int $documentId ID del documento a eliminar
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxDestroyDocument($documentId)
    {
        try {
            // Iniciar una transacción de base de datos
            DB::beginTransaction();
            
            // 1. Buscar el documento
            $document = DocumentAttachment::findOrFail($documentId);
            $fileName = $document->file_name;
            
            // 2. Verificar que pertenezca a una infracción de tráfico
            if ($document->documentable_type !== DriverTrafficConviction::class) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'El documento no pertenece a una infracción de tráfico'
                ], 400);
            }
            
            // 3. Obtener la infracción asociada
            $convictionId = $document->documentable_id;
            $conviction = DriverTrafficConviction::findOrFail($convictionId);
            
            // 4. Eliminar el documento usando el método del trait HasDocuments
            $result = $conviction->deleteDocument($documentId);
            
            // 5. Registrar la operación
            Log::info('Documento eliminado exitosamente vía AJAX', [
                'document_id' => $documentId,
                'conviction_id' => $convictionId,
                'file_name' => $fileName,
                'result' => $result
            ]);
            
            // 6. Confirmar transacción
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Documento {$fileName} eliminado correctamente"
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al eliminar documento vía AJAX', [
                'document_id' => $documentId,
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
