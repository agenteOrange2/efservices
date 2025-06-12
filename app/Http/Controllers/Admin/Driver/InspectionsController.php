<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverInspection;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Carrier;
use App\Models\DocumentAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class InspectionsController extends Controller
{
    // Vista para todas las inspecciones
    public function index(Request $request)
    {
        // Log para depuración - guardar todos los parámetros recibidos
        \Illuminate\Support\Facades\Log::info('Parámetros de filtro recibidos:', [
            'all_parameters' => $request->all(),
            'driver_filter' => $request->driver_filter,
            'carrier_filter' => $request->carrier_filter,
            'vehicle_filter' => $request->vehicle_filter,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'inspection_type' => $request->inspection_type,
            'status' => $request->status,
            'sort_field' => $request->sort_field,
            'sort_direction' => $request->sort_direction,
        ]);
        
        $query = DriverInspection::query()
            ->with(['userDriverDetail.user', 'userDriverDetail.carrier', 'vehicle']);

        // Aplicar filtros
        if ($request->filled('search_term')) {
            // Usar where con paréntesis para agrupar las condiciones OR
            $query->where(function ($q) use ($request) {
                $searchTerm = '%' . $request->search_term . '%';
                $q->where('inspection_type', 'like', $searchTerm)
                  ->orWhere('notes', 'like', $searchTerm)
                  ->orWhere('inspector_name', 'like', $searchTerm);
            });
        }

        if ($request->filled('driver_filter') && $request->driver_filter != '') {
            $query->where('user_driver_detail_id', $request->driver_filter);
        }

        if ($request->filled('carrier_filter') && $request->carrier_filter != '') {
            $query->whereHas('userDriverDetail', function ($subq) use ($request) {
                $subq->where('carrier_id', $request->carrier_filter);
            });
        }

        if ($request->filled('vehicle_filter') && $request->vehicle_filter != '') {
            $query->where('vehicle_id', $request->vehicle_filter);
        }

        if ($request->filled('date_from') && $request->date_from != '') {
            // Convertir de MM/DD/YYYY a YYYY-MM-DD
            $dateFrom = \Carbon\Carbon::createFromFormat('m/d/Y', $request->date_from)->format('Y-m-d');
            $query->whereDate('inspection_date', '>=', $dateFrom);
        }

        if ($request->filled('date_to') && $request->date_to != '') {
            // Convertir de MM/DD/YYYY a YYYY-MM-DD
            $dateTo = \Carbon\Carbon::createFromFormat('m/d/Y', $request->date_to)->format('Y-m-d');
            $query->whereDate('inspection_date', '<=', $dateTo);
        }

        if ($request->filled('inspection_type') && $request->inspection_type != '') {
            $query->where('inspection_type', $request->inspection_type);
        }

        if ($request->filled('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Ordenar resultados
        $sortField = $request->get('sort_field', 'inspection_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $inspections = $query->paginate(10);
        $drivers = UserDriverDetail::with('user')->get();
        $carriers = Carrier::where('status', 1)->get();
        $vehicles = Vehicle::all();

        // Obtener valores únicos para los filtros de desplegable
        $inspectionTypes = DriverInspection::distinct()->pluck('inspection_type')->filter()->toArray();
        $statuses = DriverInspection::distinct()->pluck('status')->filter()->toArray();

        return view('admin.drivers.inspections.index', compact(
            'inspections',
            'drivers',
            'carriers',
            'vehicles',
            'inspectionTypes',
            'statuses'
        ));
    }

    // Vista para el historial de inspecciones de un conductor específico
    public function driverHistory(UserDriverDetail $driver, Request $request)
    {
        $query = DriverInspection::where('user_driver_detail_id', $driver->id);

        // Aplicar filtros si existen
        if ($request->filled('search_term')) {
            $query->where('inspection_type', 'like', '%' . $request->search_term . '%')
                ->orWhere('notes', 'like', '%' . $request->search_term . '%')
                ->orWhere('inspector_name', 'like', '%' . $request->search_term . '%');
        }

        if ($request->filled('vehicle_filter')) {
            $query->where('vehicle_id', $request->vehicle_filter);
        }

        if ($request->filled('inspection_type')) {
            $query->where('inspection_type', $request->inspection_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Ordenar resultados
        $sortField = $request->get('sort_field', 'inspection_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $inspections = $query->paginate(10);

        // Obtener vehículos del conductor para el filtro
        $driverVehicles = Vehicle::where(function ($query) use ($driver) {
            $query->where('user_driver_detail_id', $driver->id)
                ->orWhereHas('driverInspections', function ($q) use ($driver) {
                    $q->where('user_driver_detail_id', $driver->id);
                });
        })->get();

        // Obtener valores únicos para los filtros de desplegable
        $inspectionTypes = DriverInspection::where('user_driver_detail_id', $driver->id)
            ->distinct()->pluck('inspection_type')->filter()->toArray();
        $statuses = DriverInspection::where('user_driver_detail_id', $driver->id)
            ->distinct()->pluck('status')->filter()->toArray();

        return view('admin.drivers.inspections.driver_history', compact(
            'driver',
            'inspections',
            'driverVehicles',
            'inspectionTypes',
            'statuses'
        ));
    }

    /**
     * Muestra el formulario para crear una nueva inspección
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Obtener todos los transportistas activos ordenados por nombre
        $carriers = Carrier::where('status', 1)->orderBy('name')->get();
        
        // Inicialmente no cargamos conductores, se cargarán dinámicamente por AJAX
        $drivers = [];
        
        return view('admin.drivers.inspections.create', compact('carriers', 'drivers'));
    }

    /**
     * Muestra el formulario para editar una inspección existente
     * 
     * @param \App\Models\Admin\Driver\DriverInspection $inspection
     * @return \Illuminate\View\View
     */
    public function edit(DriverInspection $inspection)
    {
        // Obtener todos los transportistas activos ordenados por nombre
        $carriers = Carrier::where('status', 1)->orderBy('name')->get();
        
        // Obtener los conductores relacionados con el transportista de esta inspección
        $carrierIdFromInspection = isset($inspection->user_driver_detail) ? $inspection->user_driver_detail->carrier_id : null;
        $drivers = [];
        
        if ($carrierIdFromInspection) {
            $drivers = UserDriverDetail::where('carrier_id', $carrierIdFromInspection)
                ->with(['user'])
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        // Obtener los documentos asociados a esta inspección usando MediaLibrary
        $documents = $inspection->getMedia('inspection_documents')->map(function($media) {
            return [
                'id' => $media->id,
                'name' => $media->file_name,
                'url' => $media->getUrl(),
                'mime_type' => $media->mime_type,
                'size' => $media->size,
                'collection' => $media->collection_name,
                'created_at' => $media->created_at->format('Y-m-d H:i:s'),
                'isExisting' => true
            ];
        })->toArray();
        
        return view('admin.drivers.inspections.edit', compact('inspection', 'carriers', 'drivers', 'documents'));
    }

    // Método para almacenar una nueva inspección
    public function store(Request $request)
    {
        //dd($request->all());
        // Validación básica
        $validated = $request->validate([
            'user_driver_detail_id' => 'required|exists:user_driver_details,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'inspection_date' => 'required|date',
            'inspection_type' => 'required|string',
            'inspection_level' => 'nullable|string',
            'inspector_name' => 'required|string|max:100',
            'inspector_number' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:200',
            'status' => 'nullable|string|max:50',
            'is_vehicle_safe_to_operate' => 'nullable|boolean',
            'comments' => 'nullable|string',
            'inspection_files' => 'nullable|string', // Campo JSON para los archivos de Livewire
            // Campos adicionales (pueden ser nulos si no se usan en este formulario)
            'defects_found' => 'nullable|string',
            'corrective_actions' => 'nullable|string',
            'is_defects_corrected' => 'nullable|boolean',
            'defects_corrected_date' => 'nullable|date',
            'corrected_by' => 'nullable|string|max:100',
        ]);

        try {
            // Iniciar transacción
            DB::beginTransaction();
            
            // Crear la inspección
            $inspection = DriverInspection::create([
                'user_driver_detail_id' => $request->user_driver_detail_id,
                'vehicle_id' => $request->vehicle_id,
                'inspection_date' => $request->inspection_date,
                'inspection_type' => $request->inspection_type,
                'inspection_level' => $request->inspection_level,
                'inspector_name' => $request->inspector_name,
                'inspector_number' => $request->inspector_number,
                'location' => $request->location,
                'status' => $request->status,
                'is_vehicle_safe_to_operate' => $request->has('is_vehicle_safe_to_operate') ? $request->is_vehicle_safe_to_operate == '1' : true,
                'notes' => $request->comments,
                // Campos adicionales (pueden ser nulos)
                'defects_found' => $request->defects_found,
                'corrective_actions' => $request->corrective_actions,
                'is_defects_corrected' => $request->has('is_defects_corrected'),
                'defects_corrected_date' => $request->defects_corrected_date,
                'corrected_by' => $request->corrected_by,
            ]);
            
            // Procesar documentos si hay datos
            if ($request->filled('inspection_files')) {
                $this->processLivewireFiles($inspection, $request->inspection_files, 'inspection_documents');
            }
            
            // Confirmación de la transacción
            DB::commit();
            
            // Mensaje de éxito
            Session::flash('success', 'Inspection record created successfully.');
            
            // Redirección a la vista index con el nombre correcto de la ruta
            return redirect()->route('admin.inspections.index');
            
        } catch (\Exception $e) {
            // Deshacer transacción en caso de error
            DB::rollBack();
            
            // Log del error
            Log::error('Error al crear inspección', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['_token'])
            ]);
            
            // Mensaje de error
            Session::flash('error', 'An error occurred while creating the inspection record: ' . $e->getMessage());
            
            // Redirección con datos antiguos
            return redirect()->back()->withInput();
        }
    }

    // Método para actualizar una inspección existente
    public function update(DriverInspection $inspection, Request $request)
    {
        // Validación básica
        $validated = $request->validate([
            'user_driver_detail_id' => 'required|exists:user_driver_details,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'inspection_date' => 'required|date',
            'inspection_type' => 'required|string',
            'inspection_level' => 'nullable|string',
            'inspector_name' => 'required|string|max:100',
            'inspector_number' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:200',
            'status' => 'nullable|string|max:50',
            'is_vehicle_safe_to_operate' => 'nullable|boolean',
            'comments' => 'nullable|string',
            'inspection_files' => 'nullable|string', // Campo JSON para los archivos de Livewire
            // Campos adicionales (pueden ser nulos si no se usan en este formulario)
            'defects_found' => 'nullable|string',
            'corrective_actions' => 'nullable|string',
            'is_defects_corrected' => 'nullable|boolean',
            'defects_corrected_date' => 'nullable|date',
            'corrected_by' => 'nullable|string|max:100',
        ]);

        try {
            // Iniciar transacción
            DB::beginTransaction();
            
            // Actualizar datos de la inspección
            $inspection->update([
                'user_driver_detail_id' => $request->user_driver_detail_id,
                'vehicle_id' => $request->vehicle_id,
                'inspection_date' => $request->inspection_date,
                'inspection_type' => $request->inspection_type,
                'inspection_level' => $request->inspection_level,
                'inspector_name' => $request->inspector_name,
                'inspector_number' => $request->inspector_number,
                'location' => $request->location,
                'status' => $request->status,
                'is_vehicle_safe_to_operate' => $request->has('is_vehicle_safe_to_operate') ? $request->is_vehicle_safe_to_operate == '1' : true,
                'notes' => $request->comments,
                // Campos adicionales (pueden ser nulos)
                'defects_found' => $request->defects_found,
                'corrective_actions' => $request->corrective_actions,
                'is_defects_corrected' => $request->has('is_defects_corrected'),
                'defects_corrected_date' => $request->defects_corrected_date,
                'corrected_by' => $request->corrected_by,
            ]);
            
            // Procesar documentos si hay datos nuevos
            if ($request->filled('inspection_files')) {
                $this->processLivewireFiles($inspection, $request->inspection_files, 'inspection_documents');
            }
            
            // Confirmación de la transacción
            DB::commit();
            
            // Mensaje de éxito
            Session::flash('success', 'Inspection record updated successfully.');
            
            // Redirección a la vista index con el nombre correcto de la ruta
            return redirect()->route('admin.inspections.index');
            
        } catch (\Exception $e) {
            // Deshacer transacción en caso de error
            DB::rollBack();
            
            // Log del error
            Log::error('Error al actualizar inspección', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'inspection_id' => $inspection->id,
                'request_data' => $request->except(['_token'])
            ]);
            
            // Mensaje de error
            Session::flash('error', 'An error occurred while updating the inspection record: ' . $e->getMessage());
            
            // Redirección con datos antiguos
            return redirect()->back()->withInput();
        }
    }

    // Método para eliminar una inspección
    public function destroy(DriverInspection $inspection)
    {
        try {
            // Eliminar todos los documentos asociados
            $inspection->deleteAllDocuments();
            
            // Eliminar el registro de la inspección
            $inspection->delete();
            
            Session::flash('success', 'Inspection record deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar inspección', [
                'error' => $e->getMessage(),
                'inspection_id' => $inspection->id,
            ]);
            
            Session::flash('error', 'An error occurred while deleting the inspection record.');
        }
        
        return redirect()->route('admin.driver-inspections.index');
    }

    // Método para eliminar un archivo específico (DEPRECATED)
    public function deleteFile($inspectionId, $documentId)
    {
        $inspection = DriverInspection::findOrFail($inspectionId);
        $inspection->deleteDocument($documentId);
        
        return response()->json(['status' => 'success']);
    }
    
    /**
     * Elimina un documento mediante una solicitud AJAX
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxDestroyDocument(Request $request)
    {
        try {
            $mediaId = $request->input('document_id');
            if (!$mediaId) {
                return response()->json(['error' => 'Media ID is required'], 400);
            }
            
            // 1. Buscar el media en la tabla media
            $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::findOrFail($mediaId);
            
            // 2. Verificar que pertenece a una inspección (tipo de modelo correcto)
            if ($media->model_type !== DriverInspection::class) {
                return response()->json(['error' => 'El documento no pertenece a una inspección'], 403);
            }
            
            $inspectionId = $media->model_id;
            $inspection = DriverInspection::find($inspectionId);
            
            if (!$inspection) {
                return response()->json(['error' => 'No se encontró la inspección asociada al documento'], 404);
            }
            
            // 3. Eliminar el media usando el método safeDeleteMedia del modelo
            $result = $inspection->safeDeleteMedia($mediaId);
            
            if (!$result) {
                return response()->json(['error' => 'No se pudo eliminar el documento'], 500);
            }
            
            return response()->json([
                'success' => true, 
                'message' => 'Documento eliminado correctamente'
            ]);
                
        } catch (\Exception $e) {
            Log::error('Error al eliminar documento mediante AJAX', [
                'media_id' => $request->input('document_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Error al eliminar documento: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Elimina un documento usando MediaLibrary
     * 
     * @param int $mediaId ID del media a eliminar
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyDocument($mediaId)
    {
        try {
            // 1. Buscar el media en la tabla media
            $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::findOrFail($mediaId);
            
            // 2. Obtener información del media antes de eliminarlo
            $fileName = $media->file_name;
            
            // 3. Verificar que pertenece a una inspección (tipo de modelo correcto)
            if ($media->model_type !== DriverInspection::class) {
                return redirect()->back()->with('error', 'El documento no pertenece a una inspección');
            }
            
            $inspectionId = $media->model_id;
            $inspection = DriverInspection::find($inspectionId);
            
            if (!$inspection) {
                return redirect()->route('admin.inspections.index')
                    ->with('error', 'No se encontró la inspección asociada al documento');
            }
            
            // 4. Eliminar el media usando el método safeDeleteMedia del modelo
            $result = $inspection->safeDeleteMedia($mediaId);
            
            if (!$result) {
                return redirect()->back()->with('error', 'No se pudo eliminar el documento');
            }
            
            return redirect()->route('admin.inspections.edit', $inspectionId)
                ->with('success', "Documento '{$fileName}' eliminado correctamente");
                
        } catch (\Exception $e) {
            Log::error('Error al eliminar documento', [
                'media_id' => $mediaId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Error al eliminar documento: ' . $e->getMessage());
        }
    }

    // Método para obtener los documentos de una inspección usando MediaLibrary
    public function getFiles(DriverInspection $inspection)
    {
        $media = $inspection->getMedia('inspection_documents');
        $files = [];
        
        foreach ($media as $item) {
            $files[] = [
                'id' => $item->id,
                'name' => $item->file_name,
                'url' => $item->getUrl(),
                'mime_type' => $item->mime_type,
                'size' => $item->size,
                'collection' => $item->collection_name,
                'thumb' => $item->getUrl('thumb'),
                'preview' => $item->getUrl('preview')
            ];
        }
        
        return response()->json($files);
    }

    // Obtener vehículos por transportista
    public function getVehiclesByCarrier($carrierId)
    {
        $vehicles = Vehicle::where('carrier_id', $carrierId)->orderBy('company_unit_number')->get();
        return response()->json($vehicles);
    }

    // Obtener vehículos por conductor basándose en su tipo (owner, third-party, company)
    public function getVehiclesByDriver($driverId)
    {
        try {
            // Obtener el conductor
            $driver = UserDriverDetail::findOrFail($driverId);
            
            // Primero vamos a obtener todos los vehículos directamente asignados al conductor
            $driverVehicles = Vehicle::where('user_driver_detail_id', $driver->id)
                ->orderBy('company_unit_number')
                ->get();
            
            // También obtenemos vehículos del carrier que no estén asignados a ningún conductor
            $unassignedCarrierVehicles = Vehicle::where('carrier_id', $driver->carrier_id)
                ->whereNull('user_driver_detail_id')
                ->orderBy('company_unit_number')
                ->get();
            
            // Combinamos ambas colecciones
            $allVehicles = $driverVehicles->merge($unassignedCarrierVehicles);
            
            // Si no hay vehículos, devolver un array vacío para evitar errores
            if ($allVehicles->isEmpty()) {
                return response()->json([]);
            }
            
            return response()->json($allVehicles->values()->all());
        } catch (\Exception $e) {
            // Registrar el error para depuración
            \Illuminate\Support\Facades\Log::error('Error al cargar vehículos: ' . $e->getMessage());
            
            // Devolver una respuesta vacía en caso de error
            return response()->json([], 200);
        }
    }

    public function getDriversByCarrier($carrier)
    {
        $drivers = UserDriverDetail::where('carrier_id', $carrier)
            ->whereHas('user', function ($query) {
                $query->where('status', 1);
            })
            ->with('user')
            ->get();

        return response()->json($drivers);
    }
    
    /**
     * Vista para todos los documentos de inspecciones
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function allDocuments(Request $request)
    {
        // Construir la consulta base para los documentos
        $query = Media::where('collection_name', 'inspection_documents')
            ->where('model_type', DriverInspection::class);
            
        // Aplicar filtros
        if ($request->filled('driver_filter') && $request->driver_filter != '') {
            $query->whereJsonContains('custom_properties->driver_id', (int)$request->driver_filter);
        }
        
        if ($request->filled('carrier_filter') && $request->carrier_filter != '') {
            // Obtener IDs de inspecciones para el carrier seleccionado
            $inspectionIds = DriverInspection::whereHas('userDriverDetail', function($q) use ($request) {
                $q->where('carrier_id', $request->carrier_filter);
            })->pluck('id')->toArray();
            
            $query->whereIn('model_id', $inspectionIds);
        }
        
        if ($request->filled('date_from') && $request->date_from != '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to') && $request->date_to != '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->filled('search_term') && $request->search_term != '') {
            $searchTerm = '%' . $request->search_term . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('file_name', 'like', $searchTerm);
            });
        }
        
        // Ordenar resultados
        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);
        
        // Paginar resultados
        $documents = $query->paginate(20);
        
        // Cargar datos relacionados para los filtros
        $drivers = UserDriverDetail::with('user')->get();
        $carriers = Carrier::where('status', 1)->get();
        
        return view('admin.drivers.inspections.all-documents', compact(
            'documents',
            'drivers',
            'carriers'
        ));
    }
    
    /**
     * Vista para los documentos de inspecciones de un conductor específico
     * 
     * @param UserDriverDetail $driver
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function driverDocuments(UserDriverDetail $driver, Request $request)
    {
        // Obtener la licencia del conductor (la primera registrada)
        $license = Media::where('collection_name', 'driver_license')
            ->where('model_type', UserDriverDetail::class)
            ->where('model_id', $driver->id)
            ->orderBy('created_at', 'asc')
            ->first();
            
        // Obtener IDs de todas las inspecciones del conductor
        $inspectionIds = DriverInspection::where('user_driver_detail_id', $driver->id)
            ->pluck('id')
            ->toArray();
        
        // Consultar documentos basados en los IDs de inspecciones
        $query = Media::where('collection_name', 'inspection_documents')
            ->where('model_type', DriverInspection::class)
            ->whereIn('model_id', $inspectionIds);
            
        if ($request->filled('date_from') && $request->date_from != '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to') && $request->date_to != '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->filled('search_term') && $request->search_term != '') {
            $searchTerm = '%' . $request->search_term . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('file_name', 'like', $searchTerm);
            });
        }
        
        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);
        
        $documents = $query->paginate(20);
        
        return view('admin.drivers.inspections.driver-documents', compact(
            'documents',
            'driver',
            'license'
        ));
    }
    
    /**
     * Método privado para procesar archivos subidos vía Livewire
     * 
     * @param DriverInspection $inspection Inspección a la que asociar los archivos
     * @param string $filesJson Datos de los archivos en formato JSON
     * @param string $collection Nombre de la colección donde guardar los archivos
     * @return int Número de archivos procesados correctamente
     */
    private function processLivewireFiles(DriverInspection $inspection, $filesJson, $collection)
    {
        $uploadedCount = 0;
        
        try {
            // Si no hay datos de archivos, salir
            if (empty($filesJson)) {
                return 0;
            }
            
            $filesArray = json_decode($filesJson, true);
            
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
                            'inspection_id' => $inspection->id
                        ]);
                        continue;
                    }
                    
                    // Usar MediaLibrary para agregar el archivo
                    $media = $inspection->addMedia($fullPath)
                        ->usingName($file['original_name'] ?? basename($fullPath))
                        ->withCustomProperties([
                            'inspection_id' => $inspection->id,
                            'driver_id' => $inspection->user_driver_detail_id,
                            'document_type' => 'inspection_document',
                            'original_name' => $file['original_name'] ?? basename($fullPath),
                            'mime_type' => $file['mime_type'] ?? mime_content_type($fullPath),
                            'size' => $file['size'] ?? filesize($fullPath)
                        ])
                        ->toMediaCollection($collection ?: 'inspection_documents');
                    
                    $uploadedCount++;
                    
                    Log::info('Documento de inspección subido correctamente', [
                        'inspection_id' => $inspection->id,
                        'media_id' => $media->id,
                        'file_name' => $media->file_name,
                        'collection' => $media->collection_name
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error al procesar documentos de inspección', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'inspection_id' => $inspection->id,
                'collection' => $collection
            ]);
        }
        
        return $uploadedCount;
    }
}
