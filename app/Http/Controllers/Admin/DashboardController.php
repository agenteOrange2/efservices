<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StatisticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Carrier;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\VehicleMaintenance;
use Carbon\CarbonPeriod;

class DashboardController extends Controller
{
    protected $statisticsService;

    public function __construct(StatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    /**
     * Mostrar el dashboard con las estadísticas
     */
    public function index(Request $request)
    {
        $startTime = microtime(true);
        
        try {
            // Valores predeterminados para filtros de fecha
            $dateRange = $request->input('date_range', 'daily');
            $customDateStart = $request->input('custom_date_start', Carbon::now()->subDays(7)->format('Y-m-d'));
            $customDateEnd = $request->input('custom_date_end', Carbon::now()->format('Y-m-d'));

            // Cache key único basado en parámetros
            $cacheKey = "dashboard_data_{$dateRange}_{$customDateStart}_{$customDateEnd}";
            
            // Intentar obtener datos del caché
            $dashboardData = Cache::remember($cacheKey, 300, function () use ($dateRange, $customDateStart, $customDateEnd) {
                $stats = $this->statisticsService->getDashboardStats();
                return [
                    'stats' => [
                        'carriers' => $stats['carriers'],
                        'drivers' => $stats['drivers'],
                        'revenue' => $stats['revenue'],
                        'activity' => $stats['activity']
                    ],
                    'chartData' => $this->prepareChartData($dateRange, $customDateStart, $customDateEnd),
                    'growthStats' => $stats['growth'],
                    'systemAlerts' => $stats['alerts'],
                ];
            });
            
            // Log performance
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            Log::info('Dashboard loaded', [
                'execution_time_ms' => $executionTime,
                'cache_hit' => Cache::has($cacheKey),
                'date_range' => $dateRange
            ]);

            return view('admin.dashboard', array_merge($dashboardData, [
                'pageTitle' => 'Dashboard',
                'breadcrumbs' => [
                    ['name' => 'Dashboard', 'url' => route('admin.dashboard')]
                ],
                'dateRange' => $dateRange,
                'customDateStart' => $customDateStart,
                'customDateEnd' => $customDateEnd,
                'stats' => $dashboardData['stats'] // Pasar $stats directamente para @json($stats)
            ]));
        } catch (\Exception $e) {
            Log::error('Error loading dashboard statistics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ]);
            
            return view('admin.dashboard')->with('error', 'Error loading dashboard data');
        }
    }


    /**
     * Exportar estadísticas a PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            $dateRange = $request->input('date_range', 'daily');
            $customDateStart = $request->input('custom_date_start', Carbon::now()->subDays(7)->format('Y-m-d'));
            $customDateEnd = $request->input('custom_date_end', Carbon::now()->format('Y-m-d'));

            // Obtener estadísticas usando el servicio
            $dashboardStats = $this->statisticsService->getDashboardStats();
            $stats = [
                'carriers' => $dashboardStats['carriers'],
                'drivers' => $dashboardStats['drivers'],
                'revenue' => $dashboardStats['revenue'],
                'activity' => $dashboardStats['activity']
            ];
            $chartData = $this->prepareChartData($dateRange, $customDateStart, $customDateEnd);
            $growthStats = $dashboardStats['growth'];

            $pdf = Pdf::loadView('admin.dashboard.pdf', compact(
                'stats', 
                'chartData', 
                'growthStats',
                'dateRange', 
                'customDateStart', 
                'customDateEnd'
            ));

            return $pdf->download('dashboard-statistics-' . now()->format('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Error exporting dashboard PDF', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Error exporting PDF');
        }
    }
    
    /**
     * Preparar datos de gráficos
     */
    private function prepareChartData($dateRange, $startDate = null, $endDate = null)
    {
        // Determinar fechas según el filtro seleccionado
        $dateFilter = $this->getDateFilter($dateRange, $startDate, $endDate);
        $start = $dateFilter['start'];
        $end = $dateFilter['end'];
        
        // Datos para gráficos principales
        $chartData = [
            // User chart data - simplified to count all users
            'users' => [
                'active' => User::where('status', 'active')->count(),
                'pending' => User::where('status', 'pending')->count(),
                'inactive' => User::whereIn('status', ['inactive', 'suspended'])->count(),
            ],
            
            // Datos para gráfico de vehículos
            'vehicles' => [
                'active' => Vehicle::where('status', 'active')->count(),
                'suspended' => Vehicle::where('status', 'suspended')->count(),
                'outOfService' => Vehicle::where('status', 'out_of_service')->count(),
            ],
            
            // Datos para gráfico de mantenimiento - asegurando que todos los estados se cuenten correctamente
            'maintenance' => [
                'completed' => VehicleMaintenance::where('status', 'completed')->count(),
                'pending' => VehicleMaintenance::whereIn('status', ['pending', 'in_progress'])->count(), // Incluir tanto 'pending' como 'in_progress'
                'upcoming' => VehicleMaintenance::where('status', 'upcoming')->count(),
                'overdue' => VehicleMaintenance::where('status', 'overdue')->count(),
            ],
            
            // Datos para gráfico de transportistas
            'carriers' => [
                'active' => Carrier::where('status', 'active')->count(),
                'pending' => Carrier::where('status', 'pending')->count(),
                'inactive' => Carrier::where('status', 'inactive')->count(),
            ],
            
            // Datos para gráfico de conductores
            'drivers' => [
                'active' => Driver::where('status', 'active')->count(),
                'pending' => Driver::where('status', 'pending')->count(),
                'inactive' => Driver::where('status', 'inactive')->count(),
            ],
        ];
        
        // Datos para gráficos de tendencias (por día, semana, mes, año)
        $trendData = [];
        
        if ($dateRange === 'daily') {
            // Últimos 7 días
            $period = CarbonPeriod::create($start, '1 day', $end);
            
            foreach ($period as $date) {
                $dayStart = $date->copy()->startOfDay();
                $dayEnd = $date->copy()->endOfDay();
                
                $trendData[] = [
                    'date' => $date->format('d/m/Y'),
                    'users' => User::whereBetween('created_at', [$dayStart, $dayEnd])->count(),
                    'carriers' => Carrier::whereBetween('created_at', [$dayStart, $dayEnd])->count(),
                    'drivers' => Driver::whereBetween('created_at', [$dayStart, $dayEnd])->count(),
                    'vehicles' => Vehicle::whereBetween('created_at', [$dayStart, $dayEnd])->count(),
                    'maintenance' => VehicleMaintenance::whereBetween('created_at', [$dayStart, $dayEnd])->count(),
                ];
            }
        } elseif ($dateRange === 'weekly') {
            // Últimas 8 semanas
            $currentWeek = Carbon::now()->startOfWeek();
            
            for ($i = 0; $i < 8; $i++) {
                $weekStart = $currentWeek->copy()->subWeeks($i)->startOfWeek();
                $weekEnd = $weekStart->copy()->endOfWeek();
                
                $trendData[] = [
                    'date' => $weekStart->format('d/m/Y') . ' - ' . $weekEnd->format('d/m/Y'),
                    'users' => User::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
                    'carriers' => Carrier::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
                    'drivers' => Driver::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
                    'vehicles' => Vehicle::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
                    'maintenance' => VehicleMaintenance::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
                ];
            }
            
            // Invertir para que sea de más antiguo a más reciente
            $trendData = array_reverse($trendData);
        } elseif ($dateRange === 'monthly') {
            // Últimos 6 meses
            $currentMonth = Carbon::now()->startOfMonth();
            
            for ($i = 0; $i < 6; $i++) {
                $monthStart = $currentMonth->copy()->subMonths($i)->startOfMonth();
                $monthEnd = $monthStart->copy()->endOfMonth();
                
                $trendData[] = [
                    'date' => $monthStart->format('M Y'),
                    'users' => User::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                    'carriers' => Carrier::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                    'drivers' => Driver::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                    'vehicles' => Vehicle::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                    'maintenance' => VehicleMaintenance::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                ];
            }
            
            // Invertir para que sea de más antiguo a más reciente
            $trendData = array_reverse($trendData);
        } elseif ($dateRange === 'yearly') {
            // Últimos 5 años
            $currentYear = Carbon::now()->startOfYear();
            
            for ($i = 0; $i < 5; $i++) {
                $yearStart = $currentYear->copy()->subYears($i)->startOfYear();
                $yearEnd = $yearStart->copy()->endOfYear();
                
                $trendData[] = [
                    'date' => $yearStart->format('Y'),
                    'users' => User::whereBetween('created_at', [$yearStart, $yearEnd])->count(),
                    'carriers' => Carrier::whereBetween('created_at', [$yearStart, $yearEnd])->count(),
                    'drivers' => Driver::whereBetween('created_at', [$yearStart, $yearEnd])->count(),
                    'vehicles' => Vehicle::whereBetween('created_at', [$yearStart, $yearEnd])->count(),
                    'maintenance' => VehicleMaintenance::whereBetween('created_at', [$yearStart, $yearEnd])->count(),
                ];
            }
            
            // Invertir para que sea de más antiguo a más reciente
            $trendData = array_reverse($trendData);
        } else {
            // Rango personalizado
            $period = CarbonPeriod::create($start, '1 day', $end);
            
            foreach ($period as $date) {
                $dayStart = $date->copy()->startOfDay();
                $dayEnd = $date->copy()->endOfDay();
                
                $trendData[] = [
                    'date' => $date->format('d/m/Y'),
                    'users' => User::whereBetween('created_at', [$dayStart, $dayEnd])->count(),
                    'carriers' => Carrier::whereBetween('created_at', [$dayStart, $dayEnd])->count(),
                    'drivers' => Driver::whereBetween('created_at', [$dayStart, $dayEnd])->count(),
                    'vehicles' => Vehicle::whereBetween('created_at', [$dayStart, $dayEnd])->count(),
                    'maintenance' => VehicleMaintenance::whereBetween('created_at', [$dayStart, $dayEnd])->count(),
                ];
            }
        }
        
        $chartData['trends'] = $trendData;
        
        return $chartData;
    }
    
    /**
     * Obtener rango de fechas según el filtro seleccionado
     */
    /**
     * Get the CSS class for a maintenance status
     *
     * @param string $status
     * @return string
     */
    private function getStatusClass($status)
    {
        switch (strtolower($status)) {
            case 'completed':
                return 'text-success';
            case 'pending':
            case 'in_progress':
                return 'text-info';
            case 'upcoming':
                return 'text-warning';
            case 'overdue':
                return 'text-danger';
            default:
                return 'text-slate-500';
        }
    }
    
    /**
     * Get the CSS class for a vehicle status
     *
     * @param string $status
     * @return string
     */
    private function getVehicleStatusClass($status)
    {
        switch (strtolower($status)) {
            case 'active':
                return 'text-success';
            case 'suspended':
                return 'text-warning';
            case 'out_of_service':
                return 'text-danger';
            default:
                return 'text-slate-500';
        }
    }

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
    // public function exportPdf(Request $request)
    // {
    //     try {
    //         // Obtener los mismos datos que en el dashboard
    //         $dateRange = $request->input('date_range', 'daily');
    //         $customDateStart = $request->input('custom_date_start', Carbon::now()->subDays(7)->format('Y-m-d'));
    //         $customDateEnd = $request->input('custom_date_end', Carbon::now()->format('Y-m-d'));
            
    //         // Preparar rango de fechas para mostrar en PDF
    //         $dateRangeText = 'Reporte Diario';
    //         if ($dateRange === 'weekly') {
    //             $dateRangeText = 'Reporte Semanal';
    //         } elseif ($dateRange === 'monthly') {
    //             $dateRangeText = 'Reporte Mensual';
    //         } elseif ($dateRange === 'yearly') {
    //             $dateRangeText = 'Reporte Anual';
    //         } elseif ($dateRange === 'custom') {
    //             $dateRangeText = 'Reporte Personalizado: ' . Carbon::parse($customDateStart)->format('d/m/Y') . ' - ' . Carbon::parse($customDateEnd)->format('d/m/Y');
    //         }
            
    //         // Datos para el PDF
    //         $activeVehicles = Vehicle::where('status', 'active')->count();
    //         $suspendedVehicles = Vehicle::where('status', 'suspended')->count();
    //         $outOfServiceVehicles = Vehicle::where('status', 'out_of_service')->count();
    //         $totalVehicles = $activeVehicles + $suspendedVehicles + $outOfServiceVehicles;
            
    //         $completedMaintenance = VehicleMaintenance::where('status', 'completed')->count();
    //         $pendingMaintenance = VehicleMaintenance::where('status', 'pending')->count();
    //         $upcomingMaintenance = VehicleMaintenance::where('status', 'upcoming')->count();
    //         $overdueMaintenance = VehicleMaintenance::where('status', 'overdue')->count();
    //         $totalMaintenance = $completedMaintenance + $pendingMaintenance + $upcomingMaintenance + $overdueMaintenance;
            
    //         // Datos recientes
    //         $recentVehicles = Vehicle::with('carrier')
    //             ->orderBy('created_at', 'desc')
    //             ->limit(5)
    //             ->get()
    //             ->map(function($vehicle) {
    //                 // Make sure status is properly formatted and translated to English
    //                 $statusLabel = ucfirst($vehicle->status);
    //                 if ($statusLabel == 'Out_of_service') {
    //                     $statusLabel = 'Out of Service';
    //                 }
                    
    //                 return [
    //                     'make' => $vehicle->make ?? 'N/A',
    //                     'model' => $vehicle->model ?? 'N/A',
    //                     'year' => $vehicle->year ?? 'N/A',
    //                     'vin' => $vehicle->vin ?? 'N/A',
    //                     'carrier' => $vehicle->carrier ? $vehicle->carrier->name : 'N/A',
    //                     'status' => $statusLabel,
    //                     'status_class' => $this->getVehicleStatusClass($vehicle->status),
    //                     'created_at' => $vehicle->created_at ? $vehicle->created_at->format('d/m/Y') : 'N/A'
    //                 ];
    //             })->toArray();
            
    //         $recentMaintenance = VehicleMaintenance::with('vehicle')
    //             ->orderBy('created_at', 'desc')
    //             ->limit(5)
    //             ->get()
    //             ->map(function($maintenance) {
    //                 // Make sure status is properly formatted and translated to English
    //                 $statusLabel = ucfirst($maintenance->status);
    //                 if ($statusLabel == 'In_progress') {
    //                     $statusLabel = 'In Progress';
    //                 } elseif ($statusLabel == 'Pending') {
    //                     $statusLabel = 'Pending';
    //                 } elseif ($statusLabel == 'Completed') {
    //                     $statusLabel = 'Completed';
    //                 } elseif ($statusLabel == 'Overdue') {
    //                     $statusLabel = 'Overdue';
    //                 } elseif ($statusLabel == 'Upcoming') {
    //                     $statusLabel = 'Upcoming';
    //                 }
                    
    //                 return [
    //                     'vehicle' => ($maintenance->vehicle ? ($maintenance->vehicle->make ?? '') . ' ' . ($maintenance->vehicle->model ?? '') . ' ' . ($maintenance->vehicle->year ?? '') : 'N/A'),
    //                     'service_date' => $maintenance->service_date ? Carbon::parse($maintenance->service_date)->format('d/m/Y') : 'N/A',
    //                     'next_service_date' => $maintenance->next_service_date ? Carbon::parse($maintenance->next_service_date)->format('d/m/Y') : 'N/A',
    //                     'cost' => '$' . number_format($maintenance->cost ?? 0, 2),
    //                     'status' => $statusLabel,
    //                     'status_class' => $this->getStatusClass($maintenance->status)
    //                 ];
    //             })->toArray();
            
    //         // Crear el PDF
    //         $pdf = PDF::loadView('admin.reports.dashboard-pdf', [
    //             'dateRange' => $dateRangeText,
    //             'generatedAt' => Carbon::now()->format('d/m/Y H:i:s'),
    //             'totalVehicles' => $totalVehicles,
    //             'activeVehicles' => $activeVehicles,
    //             'suspendedVehicles' => $suspendedVehicles,
    //             'outOfServiceVehicles' => $outOfServiceVehicles,
    //             'totalMaintenance' => $totalMaintenance,
    //             'completedMaintenance' => $completedMaintenance,
    //             'pendingMaintenance' => $pendingMaintenance,
    //             'upcomingMaintenance' => $upcomingMaintenance,
    //             'overdueMaintenance' => $overdueMaintenance,
    //             'recentVehicles' => $recentVehicles,
    //             'recentMaintenance' => $recentMaintenance
    //         ]);
            
    //         return $pdf->download('dashboard-report-' . Carbon::now()->format('Y-m-d') . '.pdf');
            
    //     } catch (\Exception $e) {
    //         // Log el error
    //         \Illuminate\Support\Facades\Log::error('Error generando PDF: ' . $e->getMessage());
    //         // Retornar respuesta de error
    //         return response()->json(['error' => 'Error al generar PDF: ' . $e->getMessage()], 500);
    //     }
    // }
    
    /**
     * Actualizar dashboard por AJAX
     */
    public function ajaxUpdate(Request $request)
    {
        try {
            // Log request for debugging
            Log::info('Dashboard AJAX update request received', $request->all());
            
            // Get filter parameters with defaults
            $dateRange = $request->input('date_range', 'daily');
            $customDateStart = $request->input('custom_date_start', Carbon::now()->subDays(7)->format('Y-m-d'));
            $customDateEnd = $request->input('custom_date_end', Carbon::now()->format('Y-m-d'));
            
            Log::info('Using filters', [
                'dateRange' => $dateRange,
                'customDateStart' => $customDateStart,
                'customDateEnd' => $customDateEnd
            ]);
            
            // Load data based on filters
            $stats = $this->loadStats($dateRange, $customDateStart, $customDateEnd);
            $chartData = $this->prepareChartData($dateRange, $customDateStart, $customDateEnd);
            
            // Log success for debugging
            Log::info('Dashboard data loaded successfully');
            
            // Return success response with data
            return response()->json([
                'success' => true,
                'stats' => $stats,
                'chartData' => $chartData
            ]);
        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('Error updating dashboard: ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());
            
            // Return error response
            return response()->json([
                'success' => false,
                'message' => 'Error loading dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }
}
