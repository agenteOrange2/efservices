<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\Admin\Driver\DriverInspection;
use App\Models\Admin\Driver\UserDriverDetail;
use App\Models\Admin\Vehicle;
use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class InspectionsController extends Controller
{
    // Método para mostrar la lista de inspecciones
    public function index(Request $request)
    {
        $query = DriverInspection::with(['driver', 'vehicle']);

        // Aplicar filtros si existen
        if ($request->filled('search_term')) {
            $query->where('inspection_type', 'like', '%' . $request->search_term . '%')
                ->orWhere('notes', 'like', '%' . $request->search_term . '%')
                ->orWhere('inspector_name', 'like', '%' . $request->search_term . '%')
                ->orWhereHas('driver', function ($q) use ($request) {
                    $q->whereHas('user', function ($q2) use ($request) {
                        $q2->where('name', 'like', '%' . $request->search_term . '%');
                    })
                    ->orWhere('last_name', 'like', '%' . $request->search_term . '%');
                });
        }

        if ($request->filled('driver_filter')) {
            $query->where('user_driver_detail_id', $request->driver_filter);
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

        // Obtener conductores y vehículos para los filtros
        $drivers = UserDriverDetail::with('user')->get();
        $vehicles = Vehicle::all();

        // Obtener valores únicos para los filtros de desplegable
        $inspectionTypes = DriverInspection::distinct()->pluck('inspection_type')->filter()->toArray();
        $statuses = DriverInspection::distinct()->pluck('status')->filter()->toArray();

        return view('admin.drivers.inspections.index', compact(
            'inspections',
            'drivers',
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

    // Vista para crear una nueva inspección
    public function create()
    {
        $carriers = Carrier::all();
        $drivers = collect(); // Inicialmente vacío, se llenará con AJAX
        
        return view('admin.drivers.inspections.create', compact('carriers', 'drivers'));
    }

    // Vista para editar una inspección existente
    public function edit(DriverInspection $inspection)
    {
        $carriers = Carrier::all();
        
        // Obtener los conductores de la transportista asociada al conductor actual
        $driver = $inspection->driver;
        $drivers = UserDriverDetail::where('carrier_id', $driver->carrier_id)->get();
        
        // Obtener documentos de la inspección usando MediaLibrary
        $documents = $inspection->getMedia('inspection_documents')->map(function ($media) {
            return [
                'id' => $media->id,
                'name' => $media->getCustomProperty('original_name', $media->name),
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
            // Eliminar todos los documentos asociados usando MediaLibrary
            $inspection->getMedia('inspection_documents')->each(function ($media) {
                $media->delete();
            });
            
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
        
        return redirect()->route('admin.inspections.index');
    }

    // Método para obtener conductores por transportista (AJAX)
    public function getDriversByCarrier(Carrier $carrier)
    {
        $drivers = UserDriverDetail::where('carrier_id', $carrier->id)->with('user')->get();
        
        return response()->json([
            'drivers' => $drivers->map(function ($driver) {
                return [
                    'id' => $driver->id,
                    'name' => $driver->user->name . ' ' . $driver->last_name
                ];
            })
        ]);
    }

    // Método para obtener vehículos por conductor (AJAX)
    public function getVehiclesByDriver(UserDriverDetail $driver)
    {
        $vehicles = Vehicle::where('carrier_id', $driver->carrier_id)->get();
        
        return response()->json([
            'vehicles' => $vehicles->map(function ($vehicle) {
                return [
                    'id' => $vehicle->id,
                    'name' => $vehicle->license_plate . ' - ' . $vehicle->brand . ' ' . $vehicle->model
                ];
            })
        ]);
    }

    // Método para procesar archivos subidos vía Livewire
    private function processLivewireFiles(DriverInspection $inspection, $filesJson, $collection)
    {
        $files = json_decode($filesJson, true);
        
        if (!is_array($files)) {
            return;
        }
        
        foreach ($files as $file) {
            if (!isset($file['temp_path'])) {
                continue;
            }
            
            $fullPath = storage_path('app/public/' . $file['temp_path']);
            
            if (!file_exists($fullPath)) {
                continue;
            }
            
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
        }
    }

    // Método para eliminar un documento (AJAX)
    public function ajaxDestroyDocument(Request $request)
    {
        try {
            $mediaId = $request->media_id;
            $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($mediaId);
            
            if (!$media) {
                return response()->json(['success' => false, 'message' => 'Document not found'], 404);
            }
            
            // Verificar que el documento pertenece a una inspección
            $inspection = DriverInspection::find($media->model_id);
            
            if (!$inspection) {
                return response()->json(['success' => false, 'message' => 'Inspection not found'], 404);
            }
            
            // Eliminar el archivo
            $media->delete();
            
            return response()->json(['success' => true, 'message' => 'Document deleted successfully']);
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar documento', [
                'error' => $e->getMessage(),
                'media_id' => $request->media_id ?? 'unknown'
            ]);
            
            return response()->json(['success' => false, 'message' => 'Error deleting document: ' . $e->getMessage()], 500);
        }
    }

    // Método para eliminar un documento (no AJAX)
    public function destroyDocument(Request $request)
    {
        try {
            $mediaId = $request->media_id;
            $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($mediaId);
            
            if (!$media) {
                Session::flash('error', 'Document not found');
                return redirect()->back();
            }
            
            // Verificar que el documento pertenece a una inspección
            $inspection = DriverInspection::find($media->model_id);
            
            if (!$inspection) {
                Session::flash('error', 'Inspection not found');
                return redirect()->back();
            }
            
            // Eliminar el archivo
            $media->delete();
            
            Session::flash('success', 'Document deleted successfully');
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar documento', [
                'error' => $e->getMessage(),
                'media_id' => $request->media_id ?? 'unknown'
            ]);
            
            Session::flash('error', 'Error deleting document: ' . $e->getMessage());
        }
        
        return redirect()->back();
    }

    // Método para obtener archivos de una inspección (AJAX)
    public function getFiles(DriverInspection $inspection)
    {
        $files = $inspection->getMedia('inspection_documents')->map(function ($media) {
            return [
                'id' => $media->id,
                'name' => $media->getCustomProperty('original_name', $media->name),
                'url' => $media->getUrl(),
                'mime_type' => $media->mime_type,
                'size' => $media->size,
                'created_at' => $media->created_at->format('Y-m-d H:i:s')
            ];
        });
        
        return response()->json(['files' => $files]);
    }
}
