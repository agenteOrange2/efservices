<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverInspection;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class InspectionsController extends Controller
{
    // Vista para todas las inspecciones
    public function index(Request $request)
    {
        $query = DriverInspection::query()
            ->with(['userDriverDetail.user', 'userDriverDetail.carrier', 'vehicle']);

        // Aplicar filtros
        if ($request->filled('search_term')) {
            $query->where('inspection_type', 'like', '%' . $request->search_term . '%')
                ->orWhere('notes', 'like', '%' . $request->search_term . '%')
                ->orWhere('inspector_name', 'like', '%' . $request->search_term . '%');
        }

        if ($request->filled('driver_filter')) {
            $query->where('user_driver_detail_id', $request->driver_filter);
        }

        if ($request->filled('carrier_filter')) {
            $query->whereHas('userDriverDetail', function ($subq) use ($request) {
                $subq->where('carrier_id', $request->carrier_filter);
            });
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

    // Método para almacenar una nueva inspección
    public function store(Request $request)
    {
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

        Session::flash('success', 'Inspection record added successfully!');

        // Redirigir a la página apropiada
        if ($request->has('redirect_to_driver')) {
            return redirect()->route('admin.drivers.inspection-history', $validated['user_driver_detail_id']);
        }

        return redirect()->route('admin.inspections.index');
    }

    // Método para actualizar una inspección existente
    public function update(DriverInspection $inspection, Request $request)
    {
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

        Session::flash('success', 'Inspection record updated successfully!');

        // Redirigir a la página apropiada
        if ($request->has('redirect_to_driver')) {
            return redirect()->route('admin.drivers.inspection-history', $inspection->user_driver_detail_id);
        }

        return redirect()->route('admin.inspections.index');
    }


    // Método para eliminar una inspección
    public function destroy(DriverInspection $inspection)
    {
        $driverId = $inspection->user_driver_detail_id;

        // Eliminar archivos adjuntos
        $inspection->clearMediaCollection('inspection_reports');
        $inspection->clearMediaCollection('defect_photos');
        $inspection->clearMediaCollection('repair_documents');

        $inspection->delete();

        Session::flash('success', 'Inspection record deleted successfully!');

        // Determinar la ruta de retorno basado en la URL de referencia
        $referer = request()->headers->get('referer');
        if (strpos($referer, 'inspection-history') !== false) {
            return redirect()->route('admin.drivers.inspection-history', $driverId);
        }

        return redirect()->route('admin.inspections.index');
    }

    // Método para eliminar un archivo específico
    public function deleteFile($inspectionId, $mediaId)
    {
        $inspection = DriverInspection::findOrFail($inspectionId);
        $media = $inspection->media()->findOrFail($mediaId);
        $media->delete();

        return response()->json(['success' => true]);
    }

    // Nuevo método para obtener los archivos de una inspección
    public function getFiles(DriverInspection $inspection)
    {
        // Cargar la relación media si no está ya cargada
        if (!$inspection->relationLoaded('media')) {
            $inspection->load('media');
        }
        return response()->json([
            'media' => $inspection->media
        ]);
    }

    // Obtener vehículos por transportista
    public function getVehiclesByCarrier(Carrier $carrier)
    {
        $vehicles = Vehicle::where('carrier_id', $carrier->id)->get();
        return response()->json($vehicles);
    }

    // Obtener vehículos por conductor
    public function getVehiclesByDriver(UserDriverDetail $driver)
    {
        $vehicles = Vehicle::where(function ($query) use ($driver) {
            $query->where('user_driver_detail_id', $driver->id)
                ->orWhere('carrier_id', $driver->carrier_id);
        })->get();

        return response()->json($vehicles);
    }

    public function getDriversByCarrier(Carrier $carrier)
    {
        $drivers = UserDriverDetail::where('carrier_id', $carrier->id)
            ->with(['user']) // Asegúrate de incluir la relación con el usuario
            ->get();

        return response()->json($drivers);
    }
}
