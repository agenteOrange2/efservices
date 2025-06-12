<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverAccident;
use App\Models\DriverAccidentReport;
use App\Models\Carrier;
use App\Models\Vehicle;
use App\Models\Company;
use App\Models\Document;
use App\Models\DocumentAttachment;
use App\Rules\NotOldThan;
use App\Traits\HasDocuments;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AccidentsController extends Controller
{
    // Vista para todos los accidentes
    public function index(Request $request)
    {
        $query = DriverAccident::query()
            ->with(['userDriverDetail.user', 'userDriverDetail.carrier']);

        // Aplicar filtros
        if ($request->filled('search_term')) {
            $query->where('nature_of_accident', 'like', '%' . $request->search_term . '%')
                ->orWhere('comments', 'like', '%' . $request->search_term . '%');
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
            // Parse date from MM/DD/YYYY format to Y-m-d for database query
            try {
                $dateFrom = Carbon::createFromFormat('m/d/Y', $request->date_from)->format('Y-m-d');
                $query->whereDate('accident_date', '>=', $dateFrom);
            } catch (\Exception $e) {
                // If date parsing fails, try the original format as fallback
                $query->whereDate('accident_date', '>=', $request->date_from);
                Log::warning('Date format parsing failed for date_from: ' . $request->date_from);
            }
        }

        if ($request->filled('date_to')) {
            // Parse date from MM/DD/YYYY format to Y-m-d for database query
            try {
                $dateTo = Carbon::createFromFormat('m/d/Y', $request->date_to)->format('Y-m-d');
                $query->whereDate('accident_date', '<=', $dateTo);
            } catch (\Exception $e) {
                // If date parsing fails, try the original format as fallback
                $query->whereDate('accident_date', '<=', $request->date_to);
                Log::warning('Date format parsing failed for date_to: ' . $request->date_to);
            }
        }

        // Ordenar resultados
        $sortField = $request->get('sort_field', 'accident_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $accidents = $query->paginate(10);
        $drivers = UserDriverDetail::with('user')->get();
        $carriers = Carrier::where('status', 1)->get();

        return view('admin.drivers.accidents.index', compact('accidents', 'drivers', 'carriers'));
    }

    // Vista para el historial de accidentes de un conductor específico
    public function driverHistory(UserDriverDetail $driver, Request $request)
    {
        $query = DriverAccident::where('user_driver_detail_id', $driver->id);

        // Aplicar filtros si existen
        if ($request->filled('search_term')) {
            $query->where('nature_of_accident', 'like', '%' . $request->search_term . '%')
                ->orWhere('comments', 'like', '%' . $request->search_term . '%');
        }

        // Ordenar resultados
        $sortField = $request->get('sort_field', 'accident_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $accidents = $query->paginate(10);

        return view('admin.drivers.accidents.driver_history', compact('driver', 'accidents'));
    }

    /**
     * Muestra el formulario para crear un nuevo accidente
     */
    public function create()
    {
        // Inicialmente no cargamos conductores, se cargarán vía AJAX cuando se seleccione un carrier
        $drivers = collect(); // Colección vacía
        $carriers = Carrier::where('status', 1)->get();
        return view('admin.drivers.accidents.create', compact('carriers', 'drivers'));
    }

    // Método para almacenar un nuevo accidente
    public function store(Request $request)
    {
        // Solución ultra simplificada - solo registrar en BD
        DB::beginTransaction();
        try {
            // Validar los datos básicos
            $validated = $request->validate([
                'user_driver_detail_id' => 'required|exists:user_driver_details,id',
                'accident_date' => 'required|date',
                'nature_of_accident' => 'required|string|max:255',
                'had_injuries' => 'boolean',
                'number_of_injuries' => 'nullable|integer|min:0',
                'had_fatalities' => 'boolean',
                'number_of_fatalities' => 'nullable|integer|min:0',
                'comments' => 'nullable|string',
            ]);

            // Crear el registro de accidente
            $accident = new DriverAccident();
            $accident->user_driver_detail_id = $request->user_driver_detail_id;
            $accident->accident_date = $request->accident_date;
            $accident->nature_of_accident = $request->nature_of_accident;
            $accident->had_injuries = $request->has('had_injuries');
            $accident->number_of_injuries = $request->has('had_injuries') ? $request->number_of_injuries : 0;
            $accident->had_fatalities = $request->has('had_fatalities');
            $accident->number_of_fatalities = $request->has('had_fatalities') ? $request->number_of_fatalities : 0;
            $accident->comments = $request->comments;
            $accident->save();

            // Solución completa: Registrar en BD Y mover archivos físicos
            if ($request->has('accident_files')) {
                $filesData = json_decode($request->accident_files, true);
                
                if (is_array($filesData)) {
                    $driverId = $accident->userDriverDetail->id;
                    $accidentId = $accident->id;
                    
                    // Crear el directorio de destino si no existe
                    $destinationDir = "public/driver/{$driverId}/accidents/{$accidentId}";
                    if (!Storage::exists($destinationDir)) {
                        Storage::makeDirectory($destinationDir);
                    }
                    
                    foreach ($filesData as $fileData) {
                        if (!empty($fileData['original_name']) && isset($fileData['path'])) {
                            try {
                                // Ruta del archivo temporal
                                $tempPath = isset($fileData['temp_path']) 
                                    ? $fileData['temp_path'] 
                                    : 'livewire-tmp/' . $fileData['path'];
                                
                                // Verificar que el archivo temporal existe
                                if (!Storage::exists($tempPath)) {
                                    // Intentar buscar en la carpeta temp directamente
                                    $tempPath = 'temp/' . basename($fileData['path']);
                                    
                                    if (!Storage::exists($tempPath)) {
                                        Log::error('Archivo temporal no encontrado (store)', [
                                            'temp_path' => $tempPath,
                                            'original_name' => $fileData['original_name']
                                        ]);
                                        continue;
                                    }
                                }
                                
                                $fileName = $fileData['original_name'];
                                $destinationPath = "{$destinationDir}/{$fileName}";
                                
                                // Mover el archivo de temp a la ubicación final
                                if (Storage::move($tempPath, $destinationPath)) {
                                    // Crear registro en la DB
                                    $document = new DocumentAttachment();
                                    $document->documentable_type = DriverAccident::class;
                                    $document->documentable_id = $accident->id;
                                    $document->file_path = $destinationPath;
                                    $document->file_name = $fileName;
                                    $document->original_name = $fileData['original_name'];
                                    $document->mime_type = $fileData['mime_type'] ?? 'application/octet-stream';
                                    $document->size = $fileData['size'] ?? Storage::size($destinationPath);
                                    $document->collection = 'accident_documents';
                                    $document->custom_properties = [
                                        'accident_id' => $accident->id,
                                        'driver_id' => $driverId,
                                        'uploaded_at' => now()->format('Y-m-d H:i:s')
                                    ];
                                    $document->save();
                                    
                                    Log::info('Documento guardado físicamente y registrado (store)', [
                                        'id' => $document->id,
                                        'from' => $tempPath,
                                        'to' => $destinationPath
                                    ]);
                                } else {
                                    Log::error('Error al mover archivo físico (store)', [
                                        'from' => $tempPath,
                                        'to' => $destinationPath
                                    ]);
                                }
                            } catch (\Exception $e) {
                                Log::error('Error al procesar documento (store)', [
                                    'error' => $e->getMessage(),
                                    'trace' => $e->getTraceAsString(),
                                    'fileData' => $fileData
                                ]);
                            }
                        }
                    }
                }
            }
            
            DB::commit();
            return redirect()->route('admin.accidents.index')
                ->with('success', 'Accident record created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear accidente', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating accident record: ' . $e->getMessage());
        }
    }

    // Muestra el formulario para editar un accidente existente
    public function edit(DriverAccident $accident)
    {
        // Cargar el carrier del conductor
        $carrierId = $accident->userDriverDetail->carrier_id;
        
        // Cargar los conductores del mismo carrier
        $drivers = UserDriverDetail::where('carrier_id', $carrierId)
            ->with('user')
            ->get();
        
        // Cargar carriers (para el dropdown)
        $carriers = Carrier::where('status', 1)->get();
        
        // Cargar documentos existentes (sistema antiguo)
        $documents = $accident->getDocuments('accident_documents');
        
        // Cargar archivos de Media Library (sistema nuevo)
        $mediaFiles = $accident->getMedia('accident-images');
        
        // Registrar información para debugging
        Log::info('Cargando archivos para accidente', [
            'accident_id' => $accident->id,
            'documentos_antiguos' => count($documents),
            'archivos_media_library' => count($mediaFiles),
            'coleccion' => 'accident-images'
        ]);
        
        return view('admin.drivers.accidents.edit', compact(
            'accident',
            'carriers',
            'drivers',
            'documents',
            'mediaFiles'
        ));
    }

    /**
     * Actualiza un registro de accidente existente
     * 
     * @param DriverAccident $accident
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(DriverAccident $accident, Request $request)
    {
        // Solución ultra simplificada - solo registrar en BD
        DB::beginTransaction();
        try {
            // Validar los datos básicos
            $validated = $request->validate([
                'user_driver_detail_id' => 'required|exists:user_driver_details,id',
                'accident_date' => 'required|date',
                'nature_of_accident' => 'required|string|max:255',
                'had_injuries' => 'boolean',
                'number_of_injuries' => 'nullable|integer|min:0',
                'had_fatalities' => 'boolean',
                'number_of_fatalities' => 'nullable|integer|min:0',
                'comments' => 'nullable|string',
            ]);
            
            // Actualizar el accidente
            $accident->user_driver_detail_id = $request->user_driver_detail_id;
            $accident->accident_date = $request->accident_date;
            $accident->nature_of_accident = $request->nature_of_accident;
            $accident->had_injuries = $request->has('had_injuries');
            $accident->number_of_injuries = $request->has('had_injuries') ? $request->number_of_injuries : 0;
            $accident->had_fatalities = $request->has('had_fatalities');
            $accident->number_of_fatalities = $request->has('had_fatalities') ? $request->number_of_fatalities : 0;
            $accident->comments = $request->comments;
            $accident->save();
            
            // Procesar archivos usando Media Library (nuevo sistema)
            if ($request->has('accident_files')) {
                $filesData = json_decode($request->accident_files, true);
                
                if (is_array($filesData)) {
                    $driverId = $accident->userDriverDetail->id;
                    $accidentId = $accident->id;
                    
                    Log::info('Procesando archivos para accidente con Media Library', [
                        'accident_id' => $accidentId,
                        'driver_id' => $driverId,
                        'num_files' => count($filesData)
                    ]);
                    
                    foreach ($filesData as $fileData) {
                        if (!empty($fileData['original_name']) && isset($fileData['path'])) {
                            try {
                                // Ruta del archivo temporal
                                $tempPath = isset($fileData['temp_path']) 
                                    ? $fileData['temp_path'] 
                                    : 'livewire-tmp/' . $fileData['path'];
                                
                                // Verificar que el archivo temporal existe
                                if (!Storage::exists($tempPath)) {
                                    // Intentar buscar en la carpeta temp directamente
                                    $tempPath = 'temp/' . basename($fileData['path']);
                                    
                                    if (!Storage::exists($tempPath)) {
                                        Log::error('Archivo temporal no encontrado', [
                                            'temp_path' => $tempPath,
                                            'original_name' => $fileData['original_name']
                                        ]);
                                        continue;
                                    }
                                }
                                
                                // Ruta completa del archivo temporal
                                $fullTempPath = Storage::path($tempPath);
                                
                                if (!file_exists($fullTempPath)) {
                                    Log::error('Archivo temporal no existe en el sistema de archivos', [
                                        'full_temp_path' => $fullTempPath
                                    ]);
                                    continue;
                                }
                                
                                // Añadir el archivo a Media Library
                                $media = $accident->addMedia($fullTempPath)
                                    ->usingName($fileData['original_name'])
                                    ->usingFileName($fileData['original_name'])
                                    ->withCustomProperties([
                                        'accident_id' => $accident->id,
                                        'driver_id' => $driverId,
                                        'accident_date' => $accident->accident_date->format('Y-m-d'),
                                        'nature' => $accident->nature_of_accident,
                                        'uploaded_at' => now()->format('Y-m-d H:i:s')
                                    ])
                                    ->toMediaCollection('accident-images');
                                
                                Log::info('Archivo añadido a Media Library correctamente', [
                                    'media_id' => $media->id,
                                    'file_name' => $media->file_name,
                                    'collection' => $media->collection_name
                                ]);
                                
                            } catch (\Exception $e) {
                                Log::error('Error al procesar archivo de accidente con Media Library', [
                                    'error' => $e->getMessage(),
                                    'trace' => $e->getTraceAsString(),
                                    'file_data' => $fileData
                                ]);
                            }
                        }
                    }
                }
            }
            
            DB::commit();
            return redirect()->route('admin.accidents.index')
                ->with('success', 'Accident record updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar accidente', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating accident record: ' . $e->getMessage());
        }
    }

    // Método para eliminar un accidente
    public function destroy(DriverAccident $accident)
    {
        try {
            // Eliminar todos los documentos asociados
            $documents = $accident->getDocuments('accident_documents');
            foreach ($documents as $document) {
                $accident->deleteDocument($document->id);
            }
            
            // Eliminar el accidente
            $accident->delete();
            
            return redirect()->route('admin.accidents.index')
                ->with('success', 'Accident record deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar accidente', [
                'accident_id' => $accident->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error deleting accident record: ' . $e->getMessage());
        }
    }

    public function getDriversByCarrier($carrier)
    {
        $drivers = UserDriverDetail::where('carrier_id', $carrier)
            ->where('status', 1) // Solo conductores activos
            ->with('user')
            ->get()
            ->map(function ($driver) {
                return [
                    'id' => $driver->id,
                    'name' => $driver->user->name . ' ' . ($driver->user->last_name ?? '')
                ];
            });
        
        return response()->json([
            'drivers' => $drivers
        ]);
    }
    
    /**
     * Muestra todos los documentos de accidentes en una vista resumida
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function documents(Request $request)
    {
        try {
            // Variable para almacenar todos los documentos (solo Media Library)
            $allDocuments = collect();
            
            // Filtros
            $specificAccidentId = $request->get('accident_id');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $carrierId = $request->get('carrier_id');
            
            // Construir consulta directa a la tabla media para mejor rendimiento
            $mediaQuery = DB::table('media')
                ->select(
                    'media.*', 
                    'driver_accidents.accident_date', 
                    'driver_accidents.nature_of_accident', 
                    'driver_accidents.id as accident_id', 
                    'driver_accidents.user_driver_detail_id',
                    'users.name', 
                    'user_driver_details.last_name',
                    'user_driver_details.carrier_id',
                    'carriers.name as carrier_name'
                )
                ->join('driver_accidents', 'media.model_id', '=', 'driver_accidents.id')
                ->join('user_driver_details', 'driver_accidents.user_driver_detail_id', '=', 'user_driver_details.id')
                ->join('users', 'user_driver_details.user_id', '=', 'users.id')
                ->join('carriers', 'user_driver_details.carrier_id', '=', 'carriers.id')
                ->where('media.model_type', '=', DriverAccident::class)
                ->where('media.collection_name', '=', 'accident-images');
            
            // Filtro por conductor
            if ($request->has('driver_id') && !empty($request->driver_id)) {
                $mediaQuery->where('driver_accidents.user_driver_detail_id', $request->driver_id);
            }
            
            // Filtro por transportista (carrier)
            if ($carrierId) {
                $mediaQuery->where('user_driver_details.carrier_id', $carrierId);
            }
            
            // Filtro por accidente específico
            if ($specificAccidentId) {
                $mediaQuery->where('driver_accidents.id', $specificAccidentId);
            }
            
            // Filtro por rango de fechas de accidente
            if ($startDate) {
                try {
                    $formattedStartDate = Carbon::createFromFormat('m-d-Y', $startDate)->startOfDay()->format('Y-m-d');
                    $mediaQuery->where('driver_accidents.accident_date', '>=', $formattedStartDate);
                } catch (\Exception $e) {
                    Log::warning('Error al formatear fecha de inicio', ['error' => $e->getMessage(), 'date' => $startDate]);
                }
            }
            
            if ($endDate) {
                try {
                    $formattedEndDate = Carbon::createFromFormat('m-d-Y', $endDate)->endOfDay()->format('Y-m-d');
                    $mediaQuery->where('driver_accidents.accident_date', '<=', $formattedEndDate);
                } catch (\Exception $e) {
                    Log::warning('Error al formatear fecha de fin', ['error' => $e->getMessage(), 'date' => $endDate]);
                }
            }
            
            // Filtro por tipo de archivo
            if ($request->has('file_type') && !empty($request->file_type)) {
                switch ($request->file_type) {
                    case 'image':
                        $mediaQuery->where('media.mime_type', 'like', 'image/%');
                        break;
                    case 'pdf':                        
                        $mediaQuery->where('media.mime_type', '=', 'application/pdf');
                        break;
                    case 'document':
                        $mediaQuery->where(function($q) {
                            $q->where('media.mime_type', 'like', '%word%')
                              ->orWhere('media.mime_type', 'like', '%excel%')
                              ->orWhere('media.mime_type', 'like', '%sheet%')
                              ->orWhere('media.mime_type', 'like', '%csv%')
                              ->orWhere('media.mime_type', 'like', '%powerpoint%')
                              ->orWhere('media.mime_type', 'like', '%presentation%');
                        });
                        break;
                }
            }
            
            // Ordenar por fecha de creación (más recientes primero)
            $mediaFiles = $mediaQuery->orderBy('media.created_at', 'desc')->get();
            
            // Transformar archivos de Media Library al formato necesario para la vista
            $mediaFilesCollection = collect($mediaFiles)->map(function ($media) {
                try {
                    // Crear un objeto con la información necesaria para la vista
                    $mediaDoc = new \stdClass();
                    $mediaDoc->id = 'media_' . $media->id; // Añadir prefijo para distinguir en previewDocument
                    $mediaDoc->file_name = $media->file_name;
                    $mediaDoc->original_name = $media->name ?? $media->file_name;
                    $mediaDoc->mime_type = $media->mime_type;
                    $mediaDoc->size = $media->size;
                    $mediaDoc->created_at = $media->created_at;
                    $mediaDoc->accident_date = $media->accident_date;
                    $mediaDoc->driver = $media->name . ' ' . ($media->last_name ?? '');
                    $mediaDoc->driver_id = $media->user_driver_detail_id;
                    $mediaDoc->accident_id = $media->accident_id;
                    $mediaDoc->nature = $media->nature_of_accident;
                    $mediaDoc->carrier_id = $media->carrier_id;
                    $mediaDoc->carrier_name = $media->carrier_name;
                    $mediaDoc->source = 'media_library';
                    $mediaDoc->media_id = $media->id;
                    
                    // Construir URL para vista previa usando la ruta del disco local
                    $diskPath = 'public/driver/' . $media->user_driver_detail_id . '/accidents/' . $media->accident_id . '/' . $media->file_name;
                    $mediaDoc->media_url = Storage::url($diskPath);
                    
                    return $mediaDoc;
                } catch (\Exception $e) {
                    Log::error('Error al transformar archivo de Media Library', [
                        'media_id' => $media->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    return null;
                }
            })->filter();
            
            // Usar la colección de Media Library como todos los documentos
            $allDocuments = $mediaFilesCollection;
            
            // Paginación manual
            $perPage = 15;
            $currentPage = $request->get('page', 1);
            $offset = ($currentPage - 1) * $perPage;
            
            $paginatedDocuments = $allDocuments->slice($offset, $perPage)->values();
            $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $paginatedDocuments,
                $allDocuments->count(),
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            
            // Obtener lista de conductores para el filtro
            $drivers = UserDriverDetail::with('user')->get();
            
            // Obtener lista de transportistas para el filtro
            $carriers = Carrier::where('status', 1)->get();
            
            return view('admin.drivers.accidents.documents', [
                'documents' => $paginator,
                'drivers' => $drivers,
                'carriers' => $carriers,
                'selectedDriver' => $request->driver_id,
                'selectedCarrier' => $carrierId,
                'selectedFileType' => $request->file_type,
                'selectedAccident' => $specificAccidentId,
                'startDate' => $startDate,
                'endDate' => $endDate
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al obtener documentos de accidentes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error al cargar los documentos: ' . $e->getMessage());
        }
    }

    /**
     * Muestra los documentos de un accidente específico
     * 
     * @param DriverAccident $accident
     * @return \Illuminate\View\View
     */
    public function showDocuments(DriverAccident $accident)
    {
        try {
            // Variable para almacenar todos los documentos (sistema antiguo + Media Library)
            $allDocuments = collect();
            
            // 1. Obtener documentos del sistema antiguo
            $query = DocumentAttachment::where('documentable_type', DriverAccident::class)
                ->where('documentable_id', $accident->id)
                ->with('documentable');
                
            // Obtener documentos del sistema antiguo
            $oldDocuments = $query->orderBy('created_at', 'desc')->get();
            
            // Incluir información adicional para cada documento del sistema antiguo
            $oldDocumentsCollection = $oldDocuments->map(function ($document) use ($accident) {
                try {
                    $document->accident_date = $accident->accident_date;
                    $document->driver = $accident->userDriverDetail->user->name . ' ' . 
                                      ($accident->userDriverDetail->user->lastname ?? '');
                    $document->driver_id = $accident->userDriverDetail->id;
                    $document->accident_id = $accident->id;
                    $document->nature = $accident->nature_of_accident;
                    $document->source = 'old_system';
                } catch (\Exception $e) {
                    Log::error('Error al obtener información adicional del documento', [
                        'document_id' => $document->id,
                        'error' => $e->getMessage()
                    ]);
                }
                return $document;
            });
            
            // Añadir documentos del sistema antiguo a la colección general
            $allDocuments = $allDocuments->concat($oldDocumentsCollection);
            
            // 2. Obtener archivos de Media Library
            $mediaFiles = $accident->getMedia('accident-images');
            
            // Transformar archivos de Media Library al mismo formato que los documentos antiguos
            $mediaFilesCollection = $mediaFiles->map(function ($media) use ($accident) {
                try {
                    // Crear un objeto similar a DocumentAttachment para mantener consistencia
                    $mediaDoc = new \stdClass();
                    $mediaDoc->id = 'media_' . $media->id; // Prefijo para distinguir
                    $mediaDoc->file_name = $media->file_name;
                    $mediaDoc->original_name = $media->file_name;
                    $mediaDoc->mime_type = $media->mime_type;
                    $mediaDoc->size = $media->size;
                    $mediaDoc->created_at = $media->created_at;
                    $mediaDoc->media_id = $media->id;
                    // Usar directamente la ruta de almacenamiento en lugar de getUrl()
                    $driverId = $accident->userDriverDetail->id;
                    $accidentId = $accident->id;
                    $mediaDoc->media_url = '/storage/driver/' . $driverId . '/accidents/' . $accidentId . '/' . $media->file_name;
                    $mediaDoc->source = 'media_library';
                    
                    // Añadir información del accidente
                    $mediaDoc->accident_date = $accident->accident_date;
                    $mediaDoc->driver = $accident->userDriverDetail->user->name . ' ' . 
                                      ($accident->userDriverDetail->user->lastname ?? '');
                    $mediaDoc->driver_id = $accident->userDriverDetail->id;
                    $mediaDoc->accident_id = $accident->id;
                    $mediaDoc->nature = $accident->nature_of_accident;
                    
                    return $mediaDoc;
                } catch (\Exception $e) {
                    Log::error('Error al procesar archivo de Media Library', [
                        'media_id' => $media->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    return null;
                }
            })->filter(); // Eliminar elementos nulos
            
            // Añadir archivos de Media Library a la colección general
            $allDocuments = $allDocuments->concat($mediaFilesCollection);
            
            // Ordenar todos los documentos por fecha (más recientes primero)
            $allDocuments = $allDocuments->sortByDesc('created_at');
            
            // Paginar manualmente
            $perPage = 15;
            $currentPage = request()->get('page', 1);
            $currentPageItems = $allDocuments->forPage($currentPage, $perPage);
            
            // Crear un paginador personalizado
            $documents = new \Illuminate\Pagination\LengthAwarePaginator(
                $currentPageItems,
                $allDocuments->count(),
                $perPage,
                $currentPage,
                ['path' => request()->url(), 'query' => request()->query()]
            );
            
            // Cargar todos los conductores para el filtro (necesario para la vista)
            $drivers = UserDriverDetail::whereHas('accidents')->with('user')->get();
            
            return view('admin.drivers.accidents.documents', compact('documents', 'drivers', 'accident'));
        } catch (\Exception $e) {
            Log::error('Error al cargar documentos del accidente', [
                'accident_id' => $accident->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Error al cargar documentos: ' . $e->getMessage());
        }
    }

    /**
     * Muestra una vista previa o descarga un documento de accidente
     * 
     * @param int $documentId ID del documento (con prefijo 'media_')
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function previewDocument($documentId)
    {
        try {
            // Verificar si es un documento de Media Library (prefijo "media_")
            if (strpos($documentId, 'media_') === 0) {
                // Extraer el ID real del media
                $mediaId = substr($documentId, 6); // Quitar "media_"
                
                // Buscar el archivo en la tabla media con información del accidente
                $media = DB::table('media')
                    ->select('media.*', 'driver_accidents.user_driver_detail_id', 'driver_accidents.id as accident_id')
                    ->join('driver_accidents', 'media.model_id', '=', 'driver_accidents.id')
                    ->where('media.id', $mediaId)
                    ->first();
                
                if (!$media) {
                    return response()->json(['error' => 'Archivo no encontrado'], 404);
                }
                
                // Construir la ruta al archivo físico
                $filePath = storage_path('app/public/driver/' . $media->user_driver_detail_id . 
                                         '/accidents/' . $media->accident_id . '/' . $media->file_name);
                
                // Verificar si el archivo existe físicamente
                if (!file_exists($filePath)) {
                    return response()->json([
                        'error' => 'Archivo físico no encontrado',
                        'path' => $filePath
                    ], 404);
                }
                
                // Determinar el tipo de contenido
                $contentType = $media->mime_type;
                
                // Servir el archivo
                return response()->file($filePath, [
                    'Content-Type' => $contentType,
                    'Content-Disposition' => 'inline; filename="' . $media->file_name . '"'
                ]);
            } else {
                // Si no tiene el prefijo 'media_', devolver error
                return response()->json(['error' => 'Formato de ID de documento inválido. Debe tener prefijo media_'], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error al mostrar vista previa del documento', [
                'document_id' => $documentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Error al mostrar vista previa: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Elimina un documento de accidente de manera segura
     * Usa eliminación directa de la tabla media para evitar eliminación en cascada
     * 
     * @param int $mediaId ID del documento en la tabla media
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteDocumentDirectly($mediaId)
    {
        try {
            // Extraer el ID numérico si viene en formato 'media_XXX'
            if (is_string($mediaId) && strpos($mediaId, 'media_') === 0) {
                $mediaId = (int) substr($mediaId, 6); // Extraer el número después de 'media_'
            }
            
            // Registrar inicio de la operación para depuración
            Log::info('Iniciando eliminación de documento', [
                'media_id' => $mediaId,
                'user_id' => Auth::id()
            ]);
            
            // Buscar el archivo en la tabla media para obtener información del archivo físico
            $media = DB::table('media')
                ->select('media.*', 'driver_accidents.user_driver_detail_id', 'driver_accidents.id as accident_id')
                ->join('driver_accidents', 'media.model_id', '=', 'driver_accidents.id')
                ->where('media.id', $mediaId)
                ->first();
            
            if (!$media) {
                Log::warning('Documento no encontrado para eliminar', ['media_id' => $mediaId]);
                return response()->json(['error' => 'Documento no encontrado'], 404);
            }
            
            // Ruta al archivo físico
            $filePath = storage_path('app/public/driver/' . $media->user_driver_detail_id . 
                                 '/accidents/' . $media->accident_id . '/' . $media->file_name);
            
            Log::info('Información del documento a eliminar', [
                'media_id' => $mediaId,
                'file_path' => $filePath,
                'file_exists' => file_exists($filePath)
            ]);
            
            // Eliminar el archivo físico si existe
            if (file_exists($filePath)) {
                unlink($filePath);
                Log::info('Archivo físico eliminado', ['file_path' => $filePath]);
            }
            
            // IMPORTANTE: Eliminar directamente de la tabla media para evitar eliminación en cascada
            // NO usar $media->delete() ya que esto eliminaría también el registro de accidente
            $deleted = DB::table('media')->where('id', $mediaId)->delete();
            
            if ($deleted) {
                // Registrar la eliminación exitosa
                Log::info('Documento eliminado correctamente de la base de datos', [
                    'media_id' => $mediaId,
                    'user_id' => Auth::id()
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Documento eliminado correctamente',
                    'media_id' => $mediaId
                ]);
            } else {
                Log::warning('No se pudo eliminar el documento de la base de datos', ['media_id' => $mediaId]);
                return response()->json([
                    'error' => 'No se pudo eliminar el documento de la base de datos'
                ], 500);
            }
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar documento', [
                'media_id' => $mediaId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Error al eliminar el documento: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Elimina un documento mediante una solicitud AJAX (para documentos tradicionales)
     *
     * @param int $mediaId ID del documento a eliminar
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxDestroyMedia($mediaId)
    {
        try {
            Log::info('Solicitud de eliminación de media recibida (accidente)', [
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
            
            // 2. Verificar que pertenezca a un accidente
            if ($mediaRecord->model_type !== DriverAccident::class) {
                Log::warning('El medio no pertenece a un accidente', [
                    'media_id' => $mediaId,
                    'model_type' => $mediaRecord->model_type
                ]);
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'El documento no pertenece a un accidente'
                ], 400);
            }
            
            // 3. Obtener el accidente asociado
            $accidentId = $mediaRecord->model_id;
            $accident = DriverAccident::findOrFail($accidentId);
            
            // 4. Usar el método safeDeleteMedia
            Log::info('Eliminando medio con safeDeleteMedia (accidente)', [
                'media_id' => $mediaId,
                'accident_id' => $accidentId
            ]);
            $result = $accident->safeDeleteMedia($mediaId);
            
            if (!$result) {
                Log::error('Error al eliminar el medio (accidente)', ['media_id' => $mediaId]);
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
            
            Log::error('Error al eliminar documento vía AJAX (accidente)', [
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
     * Elimina un documento usando nuestro nuevo sistema de documentos
     * 
     * @param int $documentId ID del documento a eliminar
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyDocument($documentId)
    {
        try {
            // 1. Buscar el documento en nuestra tabla document_attachments
            $document = \App\Models\DocumentAttachment::findOrFail($documentId);
            
            // 2. Obtener información del documento antes de eliminarlo
            $fileName = $document->original_name ?? $document->file_name;
            
            // 3. Verificar que pertenece a un accidente (tipo de modelo correcto)
            if ($document->documentable_type !== DriverAccident::class) {
                return redirect()->back()->with('error', 'El documento no pertenece a un accidente');
            }
            
            $accidentId = $document->documentable_id;
            $accident = DriverAccident::find($accidentId);
            
            if (!$accident) {
                return redirect()->route('admin.accidents.index')
                    ->with('error', 'No se encontró el accidente asociado al documento');
            }
            
            // 4. Eliminar el documento usando el método del trait HasDocuments
            $result = $accident->deleteDocument($documentId);
            
            if (!$result) {
                return redirect()->back()->with('error', 'No se pudo eliminar el documento');
            }
            
            return redirect()->route('admin.accidents.edit', $accidentId)
                ->with('success', "Documento '{$fileName}' eliminado correctamente");
                
        } catch (\Exception $e) {
            Log::error('Error al eliminar documento', [
                'document_id' => $documentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Error al eliminar documento: ' . $e->getMessage());
        }
    }

    /**
     * Subir documentos para un accidente específico usando Spatie Media Library
     * 
     * @param DriverAccident $accident El accidente al que se subirán los documentos
     * @param Request $request Solicitud con los documentos a subir o JSON de archivos temporales de Livewire
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje de éxito o error
     */
    public function storeDocuments(DriverAccident $accident, Request $request)
    {
        try {
            DB::beginTransaction();
            
            $uploadedCount = 0;
            $errors = [];
            
            // Verificar si estamos recibiendo archivos directos o JSON de Livewire
            if ($request->hasFile('documents')) {
                // Método tradicional con archivos directos
                $request->validate([
                    'documents' => 'required|array',
                    'documents.*' => 'file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx'
                ]);
                
                foreach ($request->file('documents') as $file) {
                    // Subir directamente a Media Library
                    $media = $accident->addMedia($file)
                        ->withCustomProperties([
                            'accident_id' => $accident->id,
                            'driver_id' => $accident->userDriverDetail->id,
                            'uploaded_at' => now()->format('Y-m-d H:i:s')
                        ])
                        ->toMediaCollection('accident-images');
                    
                    $uploadedCount++;
                    
                    Log::info('Documento de accidente subido correctamente', [
                        'accident_id' => $accident->id,
                        'media_id' => $media->id,
                        'file_name' => $media->file_name
                    ]);
                }
            } elseif ($request->filled('livewire_files')) {
                // Método Livewire con archivos temporales
                $livewireFiles = json_decode($request->input('livewire_files'), true);
                
                if (!is_array($livewireFiles) || empty($livewireFiles)) {
                    return redirect()->back()->with('error', 'No se recibieron archivos válidos');
                }
                
                // Procesar los archivos temporales de Livewire
                foreach ($livewireFiles as $fileData) {
                    // Verificar que tenemos la información necesaria
                    if (!isset($fileData['path']) || !isset($fileData['name'])) {
                        $errors[] = 'Datos de archivo incompletos';
                        continue;
                    }
                    
                    $tempPath = storage_path('app/' . $fileData['path']);
                    
                    // Verificar que el archivo temporal existe
                    if (!file_exists($tempPath)) {
                        $errors[] = "Archivo temporal no encontrado: {$fileData['name']}";
                        continue;
                    }
                    
                    try {
                        // Subir desde el archivo temporal a Media Library
                        $media = $accident->addMedia($tempPath)
                            ->usingName($fileData['name'])
                            ->withCustomProperties([
                                'accident_id' => $accident->id,
                                'driver_id' => $accident->userDriverDetail->id,
                                'uploaded_at' => now()->format('Y-m-d H:i:s'),
                                'original_name' => $fileData['name']
                            ])
                            ->toMediaCollection('accident-images');
                        
                        $uploadedCount++;
                        
                        Log::info('Documento de accidente subido desde Livewire', [
                            'accident_id' => $accident->id,
                            'media_id' => $media->id,
                            'file_name' => $media->file_name,
                            'original_name' => $fileData['name']
                        ]);
                    } catch (\Exception $e) {
                        $errors[] = "Error al procesar {$fileData['name']}: {$e->getMessage()}";
                        Log::error('Error al procesar archivo temporal', [
                            'file' => $fileData,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            } else {
                DB::rollBack();
                return redirect()->back()->with('error', 'No se recibieron archivos para subir');
            }
            
            DB::commit();
            
            $message = "$uploadedCount documentos subidos correctamente";
            if (!empty($errors)) {
                $message .= ", pero hubo errores con algunos archivos: " . implode(", ", $errors);
                return redirect()->back()->with('warning', $message);
            }
            
            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al subir documentos de accidente', [
                'accident_id' => $accident->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Error al subir documentos: ' . $e->getMessage());
        }
    }
}
