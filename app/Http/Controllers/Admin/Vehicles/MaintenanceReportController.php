<?php

namespace App\Http\Controllers\Admin\Vehicles;

use App\Http\Controllers\Controller;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Admin\Vehicle\VehicleMaintenance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class MaintenanceReportController extends Controller
{
    /**
     * Display the maintenance reports page
     */
    public function index(Request $request)
    {
        // Establecer valores predeterminados para evitar errores "undefined variable"
        $period = $request->input('period', 'monthly');
        $vehicleId = $request->input('vehicle_id', '');
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Ajustar período según selección
        switch ($period) {
            case 'daily':
                $startDate = Carbon::now()->format('Y-m-d');
                $endDate = Carbon::now()->format('Y-m-d');
                break;
            case 'weekly':
                $startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
                $endDate = Carbon::now()->endOfWeek()->format('Y-m-d');
                break;
            case 'monthly':
                $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
                $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
                break;
            case 'yearly':
                $startDate = Carbon::now()->startOfYear()->format('Y-m-d');
                $endDate = Carbon::now()->endOfYear()->format('Y-m-d');
                break;
            // 'custom' utilizará las fechas proporcionadas en el request
        }

        // Obtener mantenimientos según filtros
        $query = VehicleMaintenance::with('vehicle')
            ->whereBetween('service_date', [$startDate, $endDate]);

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $maintenances = $query->get();

        // Estadísticas
        $totalMaintenances = $maintenances->count();
        $totalVehiclesServiced = $maintenances->pluck('vehicle_id')->unique()->count();
        $totalCost = $maintenances->sum('cost');
        $avgCostPerVehicle = $totalVehiclesServiced > 0 ? $totalCost / $totalVehiclesServiced : 0;

        // Mantenimientos por tipo
        $maintenancesByType = $maintenances
            ->groupBy(function($item) {
                // Agrupar por primera palabra del service_tasks
                $words = explode(' ', trim($item->service_tasks));
                return $words[0];
            })
            ->map(function($group) {
                return [
                    'count' => $group->count(),
                    'cost' => $group->sum('cost')
                ];
            });

        // Vehículos para el filtro
        $vehicles = Vehicle::orderBy('make')->orderBy('model')->get();
        // Si no hay vehículos (por ejemplo en una instalación nueva), crear un array vacío para evitar errores
        if ($vehicles->isEmpty()) {
            $vehicles = collect();
        }

        return view('admin.vehicles.maintenance.reports', compact(
            'maintenances', 
            'vehicles', 
            'period', 
            'vehicleId', 
            'startDate', 
            'endDate',
            'totalMaintenances',
            'totalVehiclesServiced',
            'totalCost',
            'avgCostPerVehicle',
            'maintenancesByType'
        ));
    }

    /**
     * Generate PDF report
     */
    public function exportPDF(Request $request)
    {
        $period = $request->input('period', 'monthly');
        $vehicleId = $request->input('vehicle_id');
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Obtener mantenimientos según filtros (reutilizamos la lógica del método index)
        $query = VehicleMaintenance::with('vehicle')
            ->whereBetween('service_date', [$startDate, $endDate]);

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $maintenances = $query->get();

        // Estadísticas
        $totalMaintenances = $maintenances->count();
        $totalVehiclesServiced = $maintenances->pluck('vehicle_id')->unique()->count();
        $totalCost = $maintenances->sum('cost');
        $avgCostPerVehicle = $totalVehiclesServiced > 0 ? $totalCost / $totalVehiclesServiced : 0;

        // Mantenimientos por tipo
        $maintenancesByType = $maintenances
            ->groupBy(function($item) {
                $words = explode(' ', trim($item->service_tasks));
                return $words[0];
            })
            ->map(function($group) {
                return [
                    'count' => $group->count(),
                    'cost' => $group->sum('cost')
                ];
            });

        // Formatear fechas para el título del reporte
        $dateRange = Carbon::parse($startDate)->format('d/m/Y') . ' - ' . Carbon::parse($endDate)->format('d/m/Y');
        
        // Crear PDF
        $pdf = PDF::loadView('admin.vehicles.maintenance.pdf_report', compact(
            'maintenances',
            'dateRange',
            'period',
            'totalMaintenances',
            'totalVehiclesServiced',
            'totalCost',
            'avgCostPerVehicle',
            'maintenancesByType'
        ));

        return $pdf->download('maintenance_report_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }
}
