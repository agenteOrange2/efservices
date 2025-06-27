<?php

namespace App\Http\Controllers\Admin\Vehicles;

use App\Http\Controllers\Controller;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Admin\Vehicle\VehicleMaintenance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MaintenanceController extends Controller
{
    /**
     * Display a listing of the maintenance records.
     */
    public function index()
    {
        return view('admin.vehicles.maintenance.index');
    }

    /**
     * Show the form for creating a new maintenance record.
     */
    public function create()
    {
        // Obtener vehículos para el formulario
        $vehicles = Vehicle::orderBy('make')->orderBy('model')->get();
        
        // Tipos de mantenimiento predefinidos
        $maintenanceTypes = [
            'Preventive',
            'Corrective',
            'Inspection',
            'Oil Change',
            'Tire Rotation',
            'Brake Service',
            'Engine Service',
            'Transmission Service',
            'Other'
        ];
        
        return view('admin.vehicles.maintenance.create', compact('vehicles', 'maintenanceTypes'));
    }

    /**
     * Store a newly created maintenance record in storage.
     */
    public function store(Request $request)
    {
        // Validar los datos del formulario
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'unit' => 'required|string|min:3|max:255',
            'service_tasks' => 'required|string|min:3|max:255',
            'service_date' => 'required|date',
            'next_service_date' => 'required|date|after:service_date',
            'vendor_mechanic' => 'required|string|max:255',
            'cost' => 'required|numeric|min:0',
            'odometer' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'status' => 'boolean'
        ]);
        
        // Crear el registro de mantenimiento
        $maintenance = VehicleMaintenance::create([
            'vehicle_id' => $validated['vehicle_id'],
            'unit' => $validated['unit'],
            'service_tasks' => $validated['service_tasks'],
            'service_date' => $validated['service_date'],
            'next_service_date' => $validated['next_service_date'],
            'vendor_mechanic' => $validated['vendor_mechanic'],
            'cost' => $validated['cost'],
            'odometer' => $validated['odometer'],
            'description' => $validated['description'],
            'status' => $request->has('status')
        ]);
        
        // Procesar documentos adjuntos si existen
        // TODO: Implementar la lógica para procesar documentos adjuntos
        
        return redirect()->route('admin.maintenance.index')
            ->with('success', 'Registro de mantenimiento creado correctamente');
    }

    /**
     * Show the form for editing the specified maintenance record.
     */
    public function edit($id)
    {
        // Verificar si el registro existe
        $maintenance = VehicleMaintenance::findOrFail($id);
        
        // Obtener vehículos para el formulario
        $vehicles = Vehicle::orderBy('make')->orderBy('model')->get();
        
        // Tipos de mantenimiento predefinidos
        $maintenanceTypes = [
            'Preventive',
            'Corrective',
            'Inspection',
            'Oil Change',
            'Tire Rotation',
            'Brake Service',
            'Engine Service',
            'Transmission Service',
            'Other'
        ];
        
        return view('admin.vehicles.maintenance.edit', compact('maintenance', 'vehicles', 'maintenanceTypes'));
    }

    /**
     * Update the specified maintenance record in storage.
     */
    public function update(Request $request, $id)
    {
        // Buscar el registro de mantenimiento
        $maintenance = VehicleMaintenance::findOrFail($id);
        
        // Validar los datos del formulario
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'unit' => 'required|string|min:3|max:255',
            'service_tasks' => 'required|string|min:3|max:255',
            'service_date' => 'required|date',
            'next_service_date' => 'required|date|after:service_date',
            'vendor_mechanic' => 'required|string|max:255',
            'cost' => 'required|numeric|min:0',
            'odometer' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'status' => 'boolean'
        ]);
        
        // Actualizar el registro de mantenimiento
        $maintenance->update([
            'vehicle_id' => $validated['vehicle_id'],
            'unit' => $validated['unit'],
            'service_tasks' => $validated['service_tasks'],
            'service_date' => $validated['service_date'],
            'next_service_date' => $validated['next_service_date'],
            'vendor_mechanic' => $validated['vendor_mechanic'],
            'cost' => $validated['cost'],
            'odometer' => $validated['odometer'],
            'description' => $validated['description'],
            'status' => $request->has('status')
        ]);
        
        // Procesar documentos adjuntos si existen
        // TODO: Implementar la lógica para procesar documentos adjuntos
        
        return redirect()->route('admin.maintenance.index')
            ->with('success', 'Registro de mantenimiento actualizado correctamente');
    }

    /**
     * Display the specified maintenance record.
     */
    public function show($id)
    {
        // Buscar el mantenimiento con su relación de vehículo
        $maintenance = VehicleMaintenance::with('vehicle')->findOrFail($id);
        $vehicle = $maintenance->vehicle;
        
        return view('admin.vehicles.maintenance.show', compact('maintenance', 'vehicle'));
    }
    
    /**
     * Toggle maintenance status (completed/pending)
     */
    public function toggleStatus($id)
    {
        $maintenance = VehicleMaintenance::findOrFail($id);
        $maintenance->status = !$maintenance->status;
        $maintenance->save();
        
        return back()->with('success', 'Estado del mantenimiento actualizado.');
    }
    
    /**
     * Delete a maintenance record
     */
    public function destroy($id)
    {
        $maintenance = VehicleMaintenance::findOrFail($id);
        $maintenance->delete();
        
        return redirect()->route('admin.maintenance.index')
                ->with('success', 'Registro de mantenimiento eliminado correctamente');
    }
    
    /**
     * Export maintenance records to Excel
     */
    public function export()
    {
        // Para futura implementación de exportación
        // return (new VehicleMaintenanceExport)->download('vehicle-maintenance.xlsx');
        
        return redirect()->route('admin.maintenance.index')
            ->with('info', 'La funcionalidad de exportación estará disponible próximamente');
    }
    
    /**
     * Show maintenance reports.
     */
    public function reports()
    {
        // Para futura implementación de reportes
        return view('admin.vehicles.maintenance.reports');
    }
    
    /**
     * Show maintenance calendar.
     */
    public function calendar(Request $request)
    {
        // Aplicar filtros si están presentes
        $query = VehicleMaintenance::with('vehicle');
        
        // Filtrar por vehículo si se especificó
        if ($request->has('vehicle_id') && $request->vehicle_id) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        
        // Filtrar por estado si se especificó
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }
        
        // Obtener todos los mantenimientos con filtros aplicados
        $maintenances = $query->get();
            
        // Convertir mantenimientos al formato de eventos para el calendario
        $events = [];
        // Para almacenar los próximos mantenimientos
        $upcomingMaintenances = [];
        $today = Carbon::today();
        
        foreach ($maintenances as $maintenance) {
            // Definir la clase CSS según el estado (completado o pendiente)
            $className = $maintenance->status ? 'maintenance-completed' : 'maintenance-pending';
            
            // Obtener información del vehículo si está disponible
            $vehicleInfo = $maintenance->vehicle ? 
                $maintenance->vehicle->make . ' ' . $maintenance->vehicle->model . 
                ' (' . $maintenance->vehicle->year . ') - ' . $maintenance->vehicle->plate_number : 
                'Vehículo no especificado';
                
            // Formatear fechas para mostrar
            $serviceDateFormatted = Carbon::parse($maintenance->service_date)->format('d/m/Y');
            
            // Crear evento para la fecha de servicio
            $events[] = [
                'id' => 'service-' . $maintenance->id,
                'title' => $maintenance->service_tasks . ' - ' . $vehicleInfo,
                'start' => $maintenance->service_date, // Formato YYYY-MM-DD
                'className' => $className,
                'extendedProps' => [
                    'vehicle' => $vehicleInfo,
                    'serviceType' => $maintenance->service_tasks,
                    'serviceDate' => $serviceDateFormatted,
                    'status' => $maintenance->status,
                    'cost' => '$' . number_format($maintenance->cost, 2),
                    'description' => $maintenance->description ?? 'Sin descripción'
                ]
            ];
            
            // Crear evento para la próxima fecha de servicio si existe
            if ($maintenance->next_service_date) {
                $nextServiceDate = Carbon::parse($maintenance->next_service_date);
                $nextServiceDateFormatted = $nextServiceDate->format('d/m/Y');
                
                // Si la próxima fecha de servicio es en el futuro, añadirla a los próximos mantenimientos
                if ($nextServiceDate->gt($today)) {
                    // Agregar este mantenimiento a la lista de próximos
                    $maintenance->next_service_formatted = $nextServiceDateFormatted;
                    $upcomingMaintenances[] = $maintenance;
                }
                
                $events[] = [
                    'id' => 'next-service-' . $maintenance->id,
                    'title' => 'Próximo: ' . $maintenance->service_tasks . ' - ' . $vehicleInfo,
                    'start' => $maintenance->next_service_date, // Formato YYYY-MM-DD
                    'className' => 'maintenance-upcoming',
                    'extendedProps' => [
                        'vehicle' => $vehicleInfo,
                        'serviceType' => $maintenance->service_tasks,
                        'serviceDate' => $nextServiceDateFormatted,
                        'status' => 2, // 2 para próximos mantenimientos
                        'cost' => 'Por definir',
                        'description' => 'Próximo mantenimiento programado'
                    ]
                ];
            }
        }
        
        // Ordenar los próximos mantenimientos por fecha
        $upcomingMaintenances = collect($upcomingMaintenances)
            ->sortBy(function ($maintenance) {
                return Carbon::parse($maintenance->next_service_date)->timestamp;
            })
            ->take(5); // Mostrar solo los próximos 5 mantenimientos
        
        // Obtener todos los vehículos para el filtro
        $vehicles = Vehicle::orderBy('make')->orderBy('model')->get();
        
        // Pasar el estado seleccionado de vuelta a la vista
        $status = $request->status;
        $vehicleId = $request->vehicle_id;
            
        return view('admin.vehicles.maintenance.calendar', compact(
            'events', 
            'upcomingMaintenances', 
            'vehicles', 
            'status', 
            'vehicleId'
        ));
    }
}