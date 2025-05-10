<?php

namespace App\Http\Controllers\Carrier;

use App\Http\Controllers\Controller;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverInspection;
use App\Models\Admin\Vehicle\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CarrierDriverInspectionsController extends Controller
{
    /**
     * Mostrar la lista de inspecciones de los conductores del carrier.
     */
    public function index(Request $request)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        $query = DriverInspection::query()
            ->with(['userDriverDetail.user', 'vehicle'])
            ->whereHas('userDriverDetail', function ($q) use ($carrier) {
                $q->where('carrier_id', $carrier->id);
            });

        // Aplicar filtros
        if ($request->filled('search_term')) {
            $query->where('inspection_type', 'like', '%' . $request->search_term . '%')
                ->orWhere('notes', 'like', '%' . $request->search_term . '%')
                ->orWhere('inspector_name', 'like', '%' . $request->search_term . '%');
        }

        if ($request->filled('driver_filter')) {
            $query->where('user_driver_detail_id', $request->driver_filter);
        }

        if ($request->filled('vehicle_filter')) {
            $query->where('vehicle_id', $request->vehicle_filter);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('inspection_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('inspection_date', '<=', $request->date_to);
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
        $drivers = UserDriverDetail::where('carrier_id', $carrier->id)
            ->with('user')
            ->get();
        $vehicles = Vehicle::where('carrier_id', $carrier->id)->get();

        // Obtener valores únicos para los filtros de desplegable
        $inspectionTypes = DriverInspection::whereHas('userDriverDetail', function ($q) use ($carrier) {
                $q->where('carrier_id', $carrier->id);
            })
            ->distinct()
            ->pluck('inspection_type')
            ->filter()
            ->toArray();
            
        $statuses = DriverInspection::whereHas('userDriverDetail', function ($q) use ($carrier) {
                $q->where('carrier_id', $carrier->id);
            })
            ->distinct()
            ->pluck('status')
            ->filter()
            ->toArray();

        return view('carrier.drivers.inspections.index', compact(
            'inspections',
            'drivers',
            'vehicles',
            'carrier',
            'inspectionTypes',
            'statuses'
        ));
    }

    /**
     * Mostrar el historial de inspecciones de un conductor específico.
     */
    public function driverHistory(UserDriverDetail $driver, Request $request)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        if ($driver->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.drivers.inspections.index')
                ->with('error', 'No tienes acceso a este conductor.');
        }
        
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
            ->distinct()
            ->pluck('inspection_type')
            ->filter()
            ->toArray();
            
        $statuses = DriverInspection::where('user_driver_detail_id', $driver->id)
            ->distinct()
            ->pluck('status')
            ->filter()
            ->toArray();

        return view('carrier.drivers.inspections.driver_history', compact(
            'driver',
            'inspections',
            'driverVehicles',
            'carrier',
            'inspectionTypes',
            'statuses'
        ));
    }

    /**
     * Mostrar el formulario para crear una nueva inspección.
     */
    public function create()
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        $drivers = UserDriverDetail::where('carrier_id', $carrier->id)
            ->with('user')
            ->get();
        $vehicles = Vehicle::where('carrier_id', $carrier->id)->get();
            
        return view('carrier.drivers.inspections.create', compact('drivers', 'vehicles', 'carrier'));
    }

    /**
     * Almacenar una nueva inspección.
     */
    public function store(Request $request)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        $validated = $request->validate([
            'user_driver_detail_id' => 'required|exists:user_driver_details,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'inspection_date' => 'required|date',
            'inspection_type' => 'required|string|max:255',
            'inspector_name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'status' => 'required|string|max:255',
            'defects_found' => 'nullable|string',
            'corrective_actions' => 'nullable|string',
            'is_defects_corrected' => 'boolean',
            'defects_corrected_date' => 'nullable|date',
            'corrected_by' => 'nullable|string|max:255',
            'is_vehicle_safe_to_operate' => 'boolean',
            'notes' => 'nullable|string',
        ]);
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        $driver = UserDriverDetail::findOrFail($validated['user_driver_detail_id']);
        if ($driver->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.drivers.inspections.index')
                ->with('error', 'No tienes acceso a este conductor.');
        }
        
        // Verificar que el vehículo pertenezca al carrier del usuario autenticado
        if ($validated['vehicle_id']) {
            $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
            if ($vehicle->carrier_id !== $carrier->id) {
                return redirect()->route('carrier.drivers.inspections.index')
                    ->with('error', 'No tienes acceso a este vehículo.');
            }
        }

        // Convertir checkboxes a valores booleanos
        $validated['is_defects_corrected'] = isset($request->is_defects_corrected);
        $validated['is_vehicle_safe_to_operate'] = isset($request->is_vehicle_safe_to_operate);

        // Si hay defectos corregidos, pero no hay fecha, usar la fecha actual
        if ($validated['is_defects_corrected'] && empty($validated['defects_corrected_date'])) {
            $validated['defects_corrected_date'] = now();
        }

        // Si no hay defectos corregidos, eliminar fecha y responsable
        if (!$validated['is_defects_corrected']) {
            $validated['defects_corrected_date'] = null;
            $validated['corrected_by'] = null;
        }

        try {
            $inspection = DriverInspection::create($validated);
            
            // Procesar archivos adjuntos si están presentes
            if ($request->hasFile('inspection_reports')) {
                foreach ($request->file('inspection_reports') as $file) {
                    $inspection->addMedia($file)
                        ->usingName($file->getClientOriginalName())
                        ->usingFileName($file->getClientOriginalName())
                        ->toMediaCollection('inspection_reports');
                }
            }

            if ($request->hasFile('defect_photos')) {
                foreach ($request->file('defect_photos') as $file) {
                    $inspection->addMedia($file)
                        ->usingName($file->getClientOriginalName())
                        ->usingFileName($file->getClientOriginalName())
                        ->toMediaCollection('defect_photos');
                }
            }

            if ($request->hasFile('repair_documents')) {
                foreach ($request->file('repair_documents') as $file) {
                    $inspection->addMedia($file)
                        ->usingName($file->getClientOriginalName())
                        ->usingFileName($file->getClientOriginalName())
                        ->toMediaCollection('repair_documents');
                }
            }
            
            Session::flash('success', 'Registro de inspección añadido exitosamente.');
            
            // Redirigir a la página apropiada
            if ($request->has('redirect_to_driver')) {
                return redirect()->route('carrier.drivers.inspections.driver_history', $validated['user_driver_detail_id']);
            }
            
            return redirect()->route('carrier.drivers.inspections.index');
            
        } catch (\Exception $e) {
            Log::error('Error al crear registro de inspección', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al crear registro de inspección: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Mostrar el formulario para editar una inspección.
     */
    public function edit(DriverInspection $inspection)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        if ($inspection->userDriverDetail->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.drivers.inspections.index')
                ->with('error', 'No tienes acceso a este registro de inspección.');
        }
        
        $drivers = UserDriverDetail::where('carrier_id', $carrier->id)
            ->with('user')
            ->get();
        $vehicles = Vehicle::where('carrier_id', $carrier->id)->get();
            
        return view('carrier.drivers.inspections.edit', compact('inspection', 'drivers', 'vehicles', 'carrier'));
    }

    /**
     * Actualizar una inspección.
     */
    public function update(Request $request, DriverInspection $inspection)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        if ($inspection->userDriverDetail->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.drivers.inspections.index')
                ->with('error', 'No tienes acceso a este registro de inspección.');
        }
        
        $validated = $request->validate([
            'user_driver_detail_id' => 'required|exists:user_driver_details,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'inspection_date' => 'required|date',
            'inspection_type' => 'required|string|max:255',
            'inspector_name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'status' => 'required|string|max:255',
            'defects_found' => 'nullable|string',
            'corrective_actions' => 'nullable|string',
            'is_defects_corrected' => 'boolean',
            'defects_corrected_date' => 'nullable|date',
            'corrected_by' => 'nullable|string|max:255',
            'is_vehicle_safe_to_operate' => 'boolean',
            'notes' => 'nullable|string',
        ]);
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        $driver = UserDriverDetail::findOrFail($validated['user_driver_detail_id']);
        if ($driver->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.drivers.inspections.index')
                ->with('error', 'No tienes acceso a este conductor.');
        }
        
        // Verificar que el vehículo pertenezca al carrier del usuario autenticado
        if ($validated['vehicle_id']) {
            $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
            if ($vehicle->carrier_id !== $carrier->id) {
                return redirect()->route('carrier.drivers.inspections.index')
                    ->with('error', 'No tienes acceso a este vehículo.');
            }
        }

        // Convertir checkboxes a valores booleanos
        $validated['is_defects_corrected'] = isset($request->is_defects_corrected);
        $validated['is_vehicle_safe_to_operate'] = isset($request->is_vehicle_safe_to_operate);

        // Si hay defectos corregidos, pero no hay fecha, usar la fecha actual
        if ($validated['is_defects_corrected'] && empty($validated['defects_corrected_date'])) {
            $validated['defects_corrected_date'] = now();
        }

        // Si no hay defectos corregidos, eliminar fecha y responsable
        if (!$validated['is_defects_corrected']) {
            $validated['defects_corrected_date'] = null;
            $validated['corrected_by'] = null;
        }

        try {
            $inspection->update($validated);
            
            // Procesar archivos adjuntos si están presentes
            if ($request->hasFile('inspection_reports')) {
                foreach ($request->file('inspection_reports') as $file) {
                    $inspection->addMedia($file)
                        ->usingName($file->getClientOriginalName())
                        ->usingFileName($file->getClientOriginalName())
                        ->toMediaCollection('inspection_reports');
                }
            }

            if ($request->hasFile('defect_photos')) {
                foreach ($request->file('defect_photos') as $file) {
                    $inspection->addMedia($file)
                        ->usingName($file->getClientOriginalName())
                        ->usingFileName($file->getClientOriginalName())
                        ->toMediaCollection('defect_photos');
                }
            }

            if ($request->hasFile('repair_documents')) {
                foreach ($request->file('repair_documents') as $file) {
                    $inspection->addMedia($file)
                        ->usingName($file->getClientOriginalName())
                        ->usingFileName($file->getClientOriginalName())
                        ->toMediaCollection('repair_documents');
                }
            }
            
            Session::flash('success', 'Registro de inspección actualizado exitosamente.');
            
            // Redirigir a la página apropiada
            if ($request->has('redirect_to_driver')) {
                return redirect()->route('carrier.drivers.inspections.driver_history', $inspection->user_driver_detail_id);
            }
            
            return redirect()->route('carrier.drivers.inspections.index');
            
        } catch (\Exception $e) {
            Log::error('Error al actualizar registro de inspección', [
                'error' => $e->getMessage(),
                'inspection_id' => $inspection->id
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al actualizar registro de inspección: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Eliminar una inspección.
     */
    public function destroy(DriverInspection $inspection)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        if ($inspection->userDriverDetail->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.drivers.inspections.index')
                ->with('error', 'No tienes acceso a este registro de inspección.');
        }
        
        try {
            $driverId = $inspection->user_driver_detail_id;
            
            // Eliminar archivos adjuntos
            $inspection->clearMediaCollection('inspection_reports');
            $inspection->clearMediaCollection('defect_photos');
            $inspection->clearMediaCollection('repair_documents');
            
            $inspection->delete();
            
            Session::flash('success', 'Registro de inspección eliminado exitosamente.');
            
            // Determinar la ruta de retorno basado en la URL de referencia
            $referer = request()->headers->get('referer');
            if (strpos($referer, 'driver_history') !== false) {
                return redirect()->route('carrier.drivers.inspections.driver_history', $driverId);
            }
            
            return redirect()->route('carrier.drivers.inspections.index');
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar registro de inspección', [
                'error' => $e->getMessage(),
                'inspection_id' => $inspection->id
            ]);
            
            return redirect()->route('carrier.drivers.inspections.index')
                ->with('error', 'Error al eliminar registro de inspección: ' . $e->getMessage());
        }
    }
    
    /**
     * Eliminar un archivo específico.
     */
    public function deleteFile($inspectionId, $mediaId)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        $inspection = DriverInspection::findOrFail($inspectionId);
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        if ($inspection->userDriverDetail->carrier_id !== $carrier->id) {
            return response()->json(['error' => 'No tienes acceso a este archivo.'], 403);
        }
        
        try {
            $media = $inspection->media()->findOrFail($mediaId);
            $media->delete();
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar archivo de inspección', [
                'error' => $e->getMessage(),
                'inspection_id' => $inspectionId,
                'media_id' => $mediaId
            ]);
            
            return response()->json(['error' => 'Error al eliminar archivo: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Obtener los archivos de una inspección.
     */
    public function getFiles(DriverInspection $inspection)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        if ($inspection->userDriverDetail->carrier_id !== $carrier->id) {
            return response()->json(['error' => 'No tienes acceso a estos archivos.'], 403);
        }
        
        // Cargar la relación media si no está ya cargada
        if (!$inspection->relationLoaded('media')) {
            $inspection->load('media');
        }
        
        return response()->json([
            'media' => $inspection->media
        ]);
    }
    
    /**
     * Obtener vehículos por conductor.
     */
    public function getVehiclesByDriver(UserDriverDetail $driver)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        if ($driver->carrier_id !== $carrier->id) {
            return response()->json(['error' => 'No tienes acceso a este conductor.'], 403);
        }
        
        $vehicles = Vehicle::where(function ($query) use ($driver, $carrier) {
            $query->where('user_driver_detail_id', $driver->id)
                ->orWhere(function ($q) use ($carrier) {
                    $q->where('carrier_id', $carrier->id)
                      ->whereNull('user_driver_detail_id');
                });
        })->get();
        
        return response()->json($vehicles);
    }
}
