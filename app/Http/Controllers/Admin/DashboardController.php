<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\CarrierDocument as Document;
use App\Models\UserDriverDetail as Driver;
use App\Models\User;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Admin\Vehicle\VehicleMaintenance;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Mostrar el dashboard con las estadísticas
     */
    public function index(Request $request)
    {
        // Valores predeterminados para filtros de fecha
        $dateRange = $request->input('date_range', 'daily');
        $customDateStart = $request->input('custom_date_start', Carbon::now()->subDays(7)->format('Y-m-d'));
        $customDateEnd = $request->input('custom_date_end', Carbon::now()->format('Y-m-d'));

        // Obtener los datos según los filtros
        $stats = $this->loadStats($dateRange, $customDateStart, $customDateEnd);

        // Preparar los datos de gráficos
        $chartData = $this->prepareChartData($dateRange, $customDateStart, $customDateEnd);

        return view('admin.dashboard', compact('stats', 'chartData', 'dateRange', 'customDateStart', 'customDateEnd'));
    }

    /**
     * Cargar estadísticas según filtros
     */
    private function loadStats($dateRange, $startDate = null, $endDate = null)
    {
        // Determinar fechas según el filtro seleccionado
        $dateFilter = $this->getDateFilter($dateRange, $startDate, $endDate);
        $start = $dateFilter['start'];
        $end = $dateFilter['end'];
        
        // Totales generales
        $stats = [
            // Vehículos
            'totalVehicles' => Vehicle::count(),
            'activeVehicles' => Vehicle::where('status', 'active')->count(),
            'suspendedVehicles' => Vehicle::where('status', 'suspended')->count(),
            'outOfServiceVehicles' => Vehicle::where('status', 'out_of_service')->count(),
            
            // Mantenimiento
            'totalMaintenance' => VehicleMaintenance::count(),
            'completedMaintenance' => VehicleMaintenance::where('status', 'completed')->count(),
            'pendingMaintenance' => VehicleMaintenance::where('status', 'pending')->count(),
            'overdueMaintenance' => VehicleMaintenance::where('status', 'overdue')->count(),
            'upcomingMaintenance' => VehicleMaintenance::where('status', 'upcoming')->count(),
            
            // Carriers
            'totalCarriers' => Carrier::count(),
            'activeUserCarriers' => User::whereHas('roles', function ($query) {
                $query->where('name', 'carrier');
            })->where('status', 'active')->count(),
            'pendingUserCarriers' => User::whereHas('roles', function ($query) {
                $query->where('name', 'carrier');
            })->where('status', 'pending')->count(),
            'inactiveUserCarriers' => User::whereHas('roles', function ($query) {
                $query->where('name', 'carrier');
            })->where('status', 'inactive')->count(),
            
            // Drivers
            'totalUserDrivers' => Driver::count(),
            'activeUserDrivers' => Driver::where('status', 'active')->count(),
            'pendingUserDrivers' => Driver::where('status', 'pending')->count(),
            'inactiveUserDrivers' => Driver::where('status', 'inactive')->count(),
            
            // Otros
            'totalSuperAdmins' => User::whereHas('roles', function ($query) {
                $query->where('name', 'super-admin');
            })->count(),
            'totalDocuments' => Document::count(),
            
            // Tablas de datos recientes
            'recentCarriers' => $this->getRecentCarriers($start, $end),
            'recentUserCarriers' => $this->getRecentUserCarriers($start, $end),
            'recentUserDrivers' => $this->getRecentUserDrivers($start, $end)
        ];
        
        return $stats;
    }
    
    /**
     * Preparar datos de gráficos
     */
    private function prepareChartData($dateRange, $startDate = null, $endDate = null)
    {
        $dateFilter = $this->getDateFilter($dateRange, $startDate, $endDate);
        $start = $dateFilter['start'];
        $end = $dateFilter['end'];
        
        $vehicleData = [];
        $maintenanceData = [];
        
        // Dependiendo del rango de fechas, crear datos para los gráficos
        switch ($dateRange) {
            case 'daily':
                // Datos para los últimos 7 días
                for ($i = 6; $i >= 0; $i--) {
                    $date = Carbon::now()->subDays($i)->format('Y-m-d');
                    $vehicleData[$date] = Vehicle::whereDate('created_at', $date)->count();
                    $maintenanceData[$date] = VehicleMaintenance::whereDate('created_at', $date)->count();
                }
                break;
                
            case 'weekly':
                // Datos para las últimas 4 semanas
                for ($i = 3; $i >= 0; $i--) {
                    $startWeek = Carbon::now()->subWeeks($i)->startOfWeek();
                    $endWeek = Carbon::now()->subWeeks($i)->endOfWeek();
                    $key = $startWeek->format('M d') . ' - ' . $endWeek->format('M d');
                    
                    $vehicleData[$key] = Vehicle::whereBetween('created_at', [$startWeek, $endWeek])->count();
                    $maintenanceData[$key] = VehicleMaintenance::whereBetween('created_at', [$startWeek, $endWeek])->count();
                }
                break;
                
            case 'monthly':
                // Datos para los últimos 6 meses
                for ($i = 5; $i >= 0; $i--) {
                    $month = Carbon::now()->subMonths($i);
                    $key = $month->format('M Y');
                    
                    $vehicleData[$key] = Vehicle::whereMonth('created_at', $month->month)
                        ->whereYear('created_at', $month->year)
                        ->count();
                        
                    $maintenanceData[$key] = VehicleMaintenance::whereMonth('created_at', $month->month)
                        ->whereYear('created_at', $month->year)
                        ->count();
                }
                break;
                
            case 'yearly':
                // Datos para los últimos 5 años
                for ($i = 4; $i >= 0; $i--) {
                    $year = Carbon::now()->subYears($i)->year;
                    
                    $vehicleData[$year] = Vehicle::whereYear('created_at', $year)->count();
                    $maintenanceData[$year] = VehicleMaintenance::whereYear('created_at', $year)->count();
                }
                break;
                
            case 'custom':
                // Datos para un rango de fechas personalizado
                $period = CarbonPeriod::create($start, '1 day', $end);
                
                foreach ($period as $date) {
                    $dateStr = $date->format('Y-m-d');
                    $vehicleData[$dateStr] = Vehicle::whereDate('created_at', $dateStr)->count();
                    $maintenanceData[$dateStr] = VehicleMaintenance::whereDate('created_at', $dateStr)->count();
                }
                break;
        }
        
        return [
            'labels' => array_keys($vehicleData),
            'vehicleData' => array_values($vehicleData),
            'maintenanceData' => array_values($maintenanceData)
        ];
    }
    
    /**
     * Obtener rango de fechas según el filtro seleccionado
     */
    private function getDateFilter($dateRange, $startDate = null, $endDate = null)
    {
        $start = null;
        $end = null;
        
        switch ($dateRange) {
            case 'daily':
                $start = Carbon::now()->startOfDay();
                $end = Carbon::now()->endOfDay();
                break;
                
            case 'weekly':
                $start = Carbon::now()->startOfWeek();
                $end = Carbon::now()->endOfWeek();
                break;
                
            case 'monthly':
                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now()->endOfMonth();
                break;
                
            case 'yearly':
                $start = Carbon::now()->startOfYear();
                $end = Carbon::now()->endOfYear();
                break;
                
            case 'custom':
                $start = Carbon::parse($startDate)->startOfDay();
                $end = Carbon::parse($endDate)->endOfDay();
                break;
        }
        
        return [
            'start' => $start,
            'end' => $end
        ];
    }
    
    /**
     * Obtener carriers recientes
     */
    private function getRecentCarriers($start, $end)
    {
        $carriers = Carrier::whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        return $carriers->map(function ($carrier) {
            $statusClass = 'inline-flex items-center px-3 py-1 rounded-full text-xs ';
            
            if ($carrier->status === 'active') {
                $statusClass .= 'bg-success/20 text-success';
                $statusLabel = 'Active';
            } elseif ($carrier->status === 'pending') {
                $statusClass .= 'bg-warning/20 text-warning';
                $statusLabel = 'Pending';
            } else {
                $statusClass .= 'bg-danger/20 text-danger';
                $statusLabel = 'Inactive';
            }
            
            return [
                'id' => $carrier->id,
                'name' => $carrier->name,
                'membership' => $carrier->membership_type ?? 'Standard',
                'status' => [
                    'class' => $statusClass,
                    'label' => $statusLabel
                ],
                'created_at' => $carrier->created_at->format('M d, Y')
            ];
        });
    }
    
    /**
     * Obtener usuarios carrier recientes
     */
    private function getRecentUserCarriers($start, $end)
    {
        $users = User::whereHas('roles', function ($query) {
                $query->where('name', 'carrier');
            })
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        return $users->map(function ($user) {
            $statusClass = 'inline-flex items-center px-3 py-1 rounded-full text-xs ';
            
            if ($user->status === 'active') {
                $statusClass .= 'bg-success/20 text-success';
                $statusLabel = 'Active';
            } elseif ($user->status === 'pending') {
                $statusClass .= 'bg-warning/20 text-warning';
                $statusLabel = 'Pending';
            } else {
                $statusClass .= 'bg-danger/20 text-danger';
                $statusLabel = 'Inactive';
            }
            
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'carrier' => $user->carrier->name ?? 'N/A',
                'status' => [
                    'class' => $statusClass,
                    'label' => $statusLabel
                ],
                'created_at' => $user->created_at->format('M d, Y')
            ];
        });
    }
    
    /**
     * Obtener conductores recientes
     */
    private function getRecentUserDrivers($start, $end)
    {
        $drivers = Driver::whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        return $drivers->map(function ($driver) {
            return [
                'id' => $driver->id,
                'name' => $driver->first_name . ' ' . $driver->last_name,
                'email' => $driver->email ?? 'N/A',
                'carrier' => $driver->carrier->name ?? 'N/A',
                'created_at' => $driver->created_at->format('M d, Y')
            ];
        });
    }
    
    /**
     * Exportar dashboard en PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            // Obtener los mismos datos que en el dashboard
            $dateRange = $request->input('date_range', 'daily');
            $customDateStart = $request->input('custom_date_start', Carbon::now()->subDays(7)->format('Y-m-d'));
            $customDateEnd = $request->input('custom_date_end', Carbon::now()->format('Y-m-d'));
            
            // Preparar rango de fechas para mostrar en PDF
            $dateRangeText = 'Reporte Diario';
            if ($dateRange === 'weekly') {
                $dateRangeText = 'Reporte Semanal';
            } elseif ($dateRange === 'monthly') {
                $dateRangeText = 'Reporte Mensual';
            } elseif ($dateRange === 'yearly') {
                $dateRangeText = 'Reporte Anual';
            } elseif ($dateRange === 'custom') {
                $dateRangeText = 'Reporte Personalizado: ' . Carbon::parse($customDateStart)->format('d/m/Y') . ' - ' . Carbon::parse($customDateEnd)->format('d/m/Y');
            }
            
            // Datos para el PDF
            $activeVehicles = Vehicle::where('status', 'active')->count();
            $suspendedVehicles = Vehicle::where('status', 'suspended')->count();
            $outOfServiceVehicles = Vehicle::where('status', 'out_of_service')->count();
            $totalVehicles = $activeVehicles + $suspendedVehicles + $outOfServiceVehicles;
            
            $completedMaintenance = VehicleMaintenance::where('status', 'completed')->count();
            $pendingMaintenance = VehicleMaintenance::where('status', 'pending')->count();
            $upcomingMaintenance = VehicleMaintenance::where('status', 'upcoming')->count();
            $overdueMaintenance = VehicleMaintenance::where('status', 'overdue')->count();
            $totalMaintenance = $completedMaintenance + $pendingMaintenance + $upcomingMaintenance + $overdueMaintenance;
            
            // Datos recientes
            $recentVehicles = Vehicle::orderBy('created_at', 'desc')->limit(5)->get()
                ->map(function($vehicle) {
                    return [
                        'make' => $vehicle->make ?? 'N/A',
                        'model' => $vehicle->model ?? 'N/A',
                        'year' => $vehicle->year ?? 'N/A',
                        'vin' => $vehicle->vin ?? 'N/A',
                        'carrier' => $vehicle->carrier->name ?? 'N/A',
                        'status' => [
                            'label' => ucfirst($vehicle->status)
                        ],
                        'created_at' => $vehicle->created_at->format('d/m/Y')
                    ];
                })->toArray();
            
            $recentMaintenance = VehicleMaintenance::with('vehicle')->orderBy('created_at', 'desc')->limit(5)->get()
                ->map(function($maintenance) {
                    return [
                        'vehicle' => ($maintenance->vehicle ? ($maintenance->vehicle->make ?? '') . ' ' . ($maintenance->vehicle->model ?? '') : 'N/A'),
                        'service_date' => $maintenance->service_date ? Carbon::parse($maintenance->service_date)->format('d/m/Y') : 'N/A',
                        'next_service_date' => $maintenance->next_service_date ? Carbon::parse($maintenance->next_service_date)->format('d/m/Y') : 'N/A',
                        'cost' => '$' . number_format($maintenance->cost ?? 0, 2),
                        'status' => [
                            'label' => ucfirst($maintenance->status)
                        ]
                    ];
                })->toArray();
            
            // Crear el PDF
            $pdf = PDF::loadView('admin.reports.dashboard-pdf', [
                'dateRange' => $dateRangeText,
                'generatedAt' => Carbon::now()->format('d/m/Y H:i:s'),
                'totalVehicles' => $totalVehicles,
                'activeVehicles' => $activeVehicles,
                'suspendedVehicles' => $suspendedVehicles,
                'outOfServiceVehicles' => $outOfServiceVehicles,
                'totalMaintenance' => $totalMaintenance,
                'completedMaintenance' => $completedMaintenance,
                'pendingMaintenance' => $pendingMaintenance,
                'upcomingMaintenance' => $upcomingMaintenance,
                'overdueMaintenance' => $overdueMaintenance,
                'recentVehicles' => $recentVehicles,
                'recentMaintenance' => $recentMaintenance
            ]);
            
            return $pdf->download('dashboard-report-' . Carbon::now()->format('Y-m-d') . '.pdf');
            
        } catch (\Exception $e) {
            // Log el error
            \Illuminate\Support\Facades\Log::error('Error generando PDF: ' . $e->getMessage());
            // Retornar respuesta de error
            return response()->json(['error' => 'Error al generar PDF: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Actualizar dashboard por AJAX
     */
    public function ajaxUpdate(Request $request)
    {
        $dateRange = $request->input('date_range', 'daily');
        $customDateStart = $request->input('custom_date_start');
        $customDateEnd = $request->input('custom_date_end');
        
        $stats = $this->loadStats($dateRange, $customDateStart, $customDateEnd);
        $chartData = $this->prepareChartData($dateRange, $customDateStart, $customDateEnd);
        
        return response()->json([
            'stats' => $stats,
            'chartData' => $chartData
        ]);
    }
}
