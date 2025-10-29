<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StatisticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $statisticsService;

    public function __construct(StatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    public function index(Request $request): View
    {
        $dateRange = $request->get('date_range', 'monthly');
        $customDateStart = $request->get('custom_date_start');
        $customDateEnd = $request->get('custom_date_end');

        // Obtener estadísticas principales
        $statistics = $this->statisticsService->getDashboardStats($dateRange, $customDateStart, $customDateEnd);
        
        // Add maintenance statistics
        $statistics['maintenance'] = $this->getMaintenanceStats();
        
        // Obtener datos para gráficos
        $chartData = $this->prepareChartData($dateRange, $customDateStart, $customDateEnd);
        
        // Obtener registros recientes
        $recentRecords = $this->getRecentRecords();
        
        // Obtener alertas del sistema
        $systemAlerts = $this->getSystemAlerts();
        
        // Obtener métricas de rendimiento
        $performanceMetrics = $this->getPerformanceMetrics();

        // Preparar datos para el template específico de dashboard-overview-1
        $ecommerce = $this->prepareEcommerceData($statistics);
        $transactions = $this->prepareTransactionsData($recentRecords);

        return view('admin.dashboard', compact(
            'statistics',
            'chartData',
            'recentRecords',
            'systemAlerts',
            'performanceMetrics',
            'ecommerce',
            'transactions',
            'dateRange',
            'customDateStart',
            'customDateEnd'
        ));
    }

    private function prepareChartData($dateRange, $customDateStart, $customDateEnd)
    {
        try {
            $statistics = $this->statisticsService->getDashboardStats();
            
            // Ensure statistics is an array and has the required structure
            if (!is_array($statistics)) {
                $statistics = [];
            }
            
            // Helper function to safely get chart data
            $getChartData = function($type) use ($statistics) {
                $active = isset($statistics[$type]['active']) ? (int)$statistics[$type]['active'] : 0;
                $inactive = isset($statistics[$type]['inactive']) ? (int)$statistics[$type]['inactive'] : 0;
                
                return [
                    'labels' => ['Active', 'Inactive'],
                    'datasets' => [[
                        'data' => [$active, $inactive],
                        'backgroundColor' => [
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(239, 68, 68, 0.8)'
                        ]
                    ]]
                ];
            };
            
            return [
                'carriers' => $getChartData('carriers'),
                'drivers' => $getChartData('drivers'),
                'vehicles' => $getChartData('vehicles')
            ];
            
        } catch (\Exception $e) {
            // Log the error and return default data
            \Log::error('Error preparing chart data: ' . $e->getMessage());
            
            $defaultData = [
                'labels' => ['No Data'],
                'datasets' => [[
                    'data' => [1],
                    'backgroundColor' => ['rgba(156, 163, 175, 0.8)']
                ]]
            ];
            
            return [
                'carriers' => $defaultData,
                'drivers' => $defaultData,
                'vehicles' => $defaultData
            ];
        }
    }

    private function getRecentRecords()
    {
        return [
            'carriers' => $this->statisticsService->getRecentCarriers(5), // Usar el nuevo método
            'drivers' => $this->statisticsService->getRecentDrivers(5), // Usar el nuevo método
            'vehicles' => $this->statisticsService->getRecentVehicles(5), // Usar el nuevo método
            'maintenance' => $this->statisticsService->getRecentRecords('maintenance', 5)
        ];
    }

    private function getSystemAlerts()
    {
        return $this->statisticsService->getSystemAlerts();
    }

    private function getPerformanceMetrics()
    {
        return $this->statisticsService->getPerformanceMetrics();
    }

    /**
     * Preparar datos para la sección de ecommerce/performance insights
     */
    private function prepareEcommerceData($statistics)
    {
        return collect([
            [
                'title' => 'Carrier Network Growth',
                'subtitle' => 'New registrations this month',
                'icon' => 'Truck',
                'link' => 'View carrier details',
                'images' => [] // Placeholder para imágenes de usuarios
            ],
            [
                'title' => 'Driver Performance',
                'subtitle' => 'Active drivers and ratings',
                'icon' => 'Users',
                'link' => 'View driver metrics',
                'images' => []
            ],
            [
                'title' => 'Fleet Management',
                'subtitle' => 'Vehicle status and maintenance',
                'icon' => 'Settings',
                'link' => 'View fleet status',
                'images' => []
            ],
            [
                'title' => 'Revenue Analytics',
                'subtitle' => 'Monthly earnings and trends',
                'icon' => 'DollarSign',
                'link' => 'View revenue report',
                'images' => []
            ],
            [
                'title' => 'Maintenance Schedule',
                'subtitle' => 'Upcoming and overdue services',
                'icon' => 'Wrench',
                'link' => 'View maintenance',
                'images' => []
            ]
        ]);
    }

    /**
     * Preparar datos para la tabla de transacciones/órdenes recientes
     */
    private function prepareTransactionsData($recentRecords)
    {
        $transactions = collect();

        // Agregar carriers recientes
        if (isset($recentRecords['carriers'])) {
            foreach ($recentRecords['carriers'] as $carrier) {
                $transactions->push([
                    'orderId' => 'CAR-' . str_pad($carrier['id'] ?? 0, 4, '0', STR_PAD_LEFT),
                    'category' => [
                        'name' => 'Carrier Registration',
                        'icon' => 'Truck'
                    ],
                    'user' => [
                        'name' => $carrier['company_name'] ?? 'Unknown Carrier'
                    ],
                    'products' => [
                        [
                            'name' => 'Carrier Service',
                            'images' => []
                        ]
                    ],
                    'amount' => '$' . number_format(($carrier['registration_fee'] ?? 0), 2),
                    'status' => $carrier['status'] ?? 'active',
                    'date' => $carrier['created_at'] ?? now(),
                    'type' => 'carrier'
                ]);
            }
        }

        // Agregar drivers recientes
        if (isset($recentRecords['drivers'])) {
            foreach ($recentRecords['drivers'] as $driver) {
                $transactions->push([
                    'orderId' => 'DRV-' . str_pad($driver['id'] ?? 0, 4, '0', STR_PAD_LEFT),
                    'category' => [
                        'name' => 'Driver Registration',
                        'icon' => 'User'
                    ],
                    'user' => [
                        'name' => ($driver['first_name'] ?? '') . ' ' . ($driver['last_name'] ?? '')
                    ],
                    'products' => [
                        [
                            'name' => 'Driver License',
                            'images' => []
                        ]
                    ],
                    'amount' => '$' . number_format(($driver['license_fee'] ?? 0), 2),
                    'status' => $driver['status'] ?? 'active',
                    'date' => $driver['created_at'] ?? now(),
                    'type' => 'driver'
                ]);
            }
        }

        // Agregar vehículos recientes
        if (isset($recentRecords['vehicles'])) {
            foreach ($recentRecords['vehicles'] as $vehicle) {
                $transactions->push([
                    'orderId' => 'VEH-' . str_pad($vehicle['id'] ?? 0, 4, '0', STR_PAD_LEFT),
                    'category' => [
                        'name' => 'Vehicle Registration',
                        'icon' => 'Car'
                    ],
                    'user' => [
                        'name' => ($vehicle['make'] ?? 'Unknown') . ' ' . ($vehicle['model'] ?? 'Vehicle')
                    ],
                    'products' => [
                        [
                            'name' => 'Vehicle Registration',
                            'images' => []
                        ]
                    ],
                    'amount' => '$' . number_format(($vehicle['registration_cost'] ?? 0), 2),
                    'status' => $vehicle['status'] ?? 'active',
                    'date' => $vehicle['created_at'] ?? now(),
                    'type' => 'vehicle'
                ]);
            }
        }

        // Agregar mantenimientos recientes
        if (isset($recentRecords['maintenance'])) {
            foreach ($recentRecords['maintenance'] as $maintenance) {
                $transactions->push([
                    'orderId' => 'MNT-' . str_pad($maintenance['id'] ?? 0, 4, '0', STR_PAD_LEFT),
                    'category' => [
                        'name' => 'Maintenance Order',
                        'icon' => 'Wrench'
                    ],
                    'user' => [
                        'name' => $maintenance['vehicle_info'] ?? 'Vehicle Maintenance'
                    ],
                    'products' => [
                        [
                            'name' => $maintenance['service_type'] ?? 'General Service',
                            'images' => []
                        ]
                    ],
                    'amount' => '$' . number_format(($maintenance['cost'] ?? 0), 2),
                    'status' => $maintenance['status'] ?? 'pending',
                    'date' => $maintenance['scheduled_date'] ?? now(),
                    'type' => 'maintenance'
                ]);
            }
        }

        // Ordenar por fecha más reciente y tomar los primeros 10
        return $transactions->sortByDesc('date')->take(10);
    }

    /**
     * Get maintenance statistics for the dashboard
     */
    private function getMaintenanceStats()
    {
        $maintenanceModel = \App\Models\Admin\Vehicle\VehicleMaintenance::class;
        
        return [
            'total' => $maintenanceModel::count(),
            'pending' => $maintenanceModel::where('status', false)->count(),
            'completed' => $maintenanceModel::where('status', true)->count(),
            'overdue' => $maintenanceModel::where('status', false)
                ->where('next_service_date', '<', now())
                ->count(),
            'upcoming' => $maintenanceModel::where('status', false)
                ->where('next_service_date', '>=', now())
                ->where('next_service_date', '<=', now()->addDays(30))
                ->count()
        ];
    }
}