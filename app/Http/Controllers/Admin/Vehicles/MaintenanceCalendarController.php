<?php

namespace App\Http\Controllers\Admin\Vehicles;

use App\Http\Controllers\Controller;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Admin\Vehicle\VehicleMaintenance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MaintenanceCalendarController extends Controller
{
    /**
     * Display the maintenance calendar view
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $vehicles = Vehicle::orderBy('make')->get();
        // Si no hay vehículos (por ejemplo en una instalación nueva), crear un array vacío para evitar errores
        if ($vehicles->isEmpty()) {
            $vehicles = collect();
        }
        $vehicleId = $request->input('vehicle_id');
        $status = $request->input('status');

        $query = VehicleMaintenance::query()
            ->with('vehicle');

        // Aplicar filtro de vehículo si se seleccionó uno
        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        // Aplicar filtro de estado si se seleccionó
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        // Obtener todos los mantenimientos para mostrar en el calendario
        $maintenances = $query->get();

        // Formatear los eventos para el calendario
        $events = $maintenances->map(function ($maintenance) {
            $color = $maintenance->status ? '#34C38F' : '#F1556C'; // verde para completado, rojo para pendiente
            
            return [
                'id' => $maintenance->id,
                'title' => $maintenance->service_tasks . ' - ' . $maintenance->vehicle->make . ' ' . $maintenance->vehicle->model,
                'start' => $maintenance->service_date->format('Y-m-d'),
                'end' => $maintenance->service_date->format('Y-m-d'),
                'url' => route('admin.maintenance.edit', $maintenance->id),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'vehicle' => $maintenance->vehicle->make . ' ' . $maintenance->vehicle->model,
                    'vehicleId' => $maintenance->vehicle_id,
                    'status' => $maintenance->status,
                    'cost' => $maintenance->cost,
                ]
            ];
        });

        // Formatear los mantenimientos futuros (próximos servicios)
        $upcomingMaintenances = $maintenances->filter(function ($maintenance) {
            return !$maintenance->status && $maintenance->service_date->greaterThanOrEqualTo(Carbon::today());
        })->sortBy('service_date')->take(5);

        return view('admin.vehicles.maintenance.calendar', compact(
            'vehicles', 
            'vehicleId', 
            'status', 
            'events', 
            'upcomingMaintenances'
        ));
    }
    
    /**
     * Obtiene los eventos de mantenimiento para el calendario
     * en formato JSON (para llamadas AJAX)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEvents(Request $request)
    {
        $start = $request->input('start');
        $end = $request->input('end');
        $vehicleId = $request->input('vehicle_id');
        $status = $request->input('status');
        
        $query = VehicleMaintenance::query()
            ->with('vehicle')
            ->whereBetween('service_date', [Carbon::parse($start), Carbon::parse($end)]);
            
        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }
        
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }
        
        $maintenances = $query->get();
        
        $events = $maintenances->map(function ($maintenance) {
            $color = $maintenance->status ? '#34C38F' : '#F1556C';
            
            return [
                'id' => $maintenance->id,
                'title' => $maintenance->service_tasks . ' - ' . $maintenance->vehicle->make . ' ' . $maintenance->vehicle->model,
                'start' => $maintenance->service_date->format('Y-m-d'),
                'end' => $maintenance->service_date->format('Y-m-d'),
                'url' => route('admin.maintenance.edit', $maintenance->id),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'vehicle' => $maintenance->vehicle->make . ' ' . $maintenance->vehicle->model,
                    'vehicleId' => $maintenance->vehicle_id,
                    'status' => $maintenance->status,
                    'cost' => $maintenance->cost,
                ]
            ];
        });
        
        return response()->json($events);
    }
}
