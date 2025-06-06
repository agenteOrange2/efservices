<?php

namespace App\Http\Controllers\Admin\Vehicles;

use App\Http\Controllers\Controller;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Admin\Vehicle\VehicleMaintenance;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    /**
     * Display a listing of the maintenance records.
     */
    public function index()
    {
        // Pasamos a la vista donde se renderizará el componente Livewire
        return view('admin.vehicles.maintenance.index');
    }

    /**
     * Show the form for creating a new maintenance record.
     */
    public function create()
    {
        // Pasamos a la vista donde se renderizará el componente Livewire para creación
        return view('admin.vehicles.maintenance.create');
    }

    /**
     * Show the form for editing the specified maintenance record.
     */
    public function edit($id)
    {
        // Verificar si el registro existe
        $maintenance = VehicleMaintenance::findOrFail($id);
        
        // Pasamos a la vista donde se renderizará el componente Livewire para edición
        return view('admin.vehicles.maintenance.edit', ['id' => $id]);
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
    public function calendar()
    {
        // Para futura implementación de calendario de mantenimientos
        $maintenances = VehicleMaintenance::with('vehicle')
            ->where('next_service_date', '>=', now())
            ->get();
            
        return view('admin.vehicles.maintenance.calendar', compact('maintenances'));
    }
}