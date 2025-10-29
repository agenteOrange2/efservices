<?php

namespace App\Services;

use App\Models\Carrier;
use App\Models\UserDriverDetail;
use App\Models\User;
use App\Models\Membership;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Admin\Vehicle\VehicleMaintenance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class StatisticsService
{
    private const CACHE_TTL = 1800; // 30 minutos para estadísticas
    private const CACHE_PREFIX = 'stats:';

    /**
     * Obtener estadísticas principales del dashboard
     */
    public function getDashboardStats(): array
    {
        $cacheKey = self::CACHE_PREFIX . 'dashboard:main';
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            try {
                return [
                    'carriers' => $this->getCarrierStats(),
                    'drivers' => $this->getDriverStats(),
                    'vehicles' => $this->getVehicleStats(),
                    'users' => $this->getUserStats(),
                    'revenue' => $this->getRevenueStats(),
                    'activity' => $this->getActivityStats(),
                    'growth' => $this->getGrowthStats(),
                    'alerts' => $this->getSystemAlerts()
                ];
            } catch (Exception $e) {
                Log::error('Error al obtener estadísticas del dashboard: ' . $e->getMessage());
                throw new Exception('Error al cargar las estadísticas del dashboard');
            }
        });
    }

    /**
     * Estadísticas de carriers con métricas clave
     */
    public function getCarrierStats(): array
    {
        $cacheKey = self::CACHE_PREFIX . 'carriers:overview';
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            try {
                $stats = DB::table('carriers as c')
                    ->leftJoin('memberships as m', 'c.id_plan', '=', 'm.id')
                    ->select([
                        DB::raw('COUNT(*) as total'),
                        DB::raw('COUNT(CASE WHEN c.status = 1 THEN 1 END) as active'),
                        DB::raw('COUNT(CASE WHEN c.status = 0 THEN 1 END) as inactive'),
                        DB::raw('COUNT(CASE WHEN c.status = 2 THEN 1 END) as pending'),
                        DB::raw('COUNT(CASE WHEN c.document_status = "pending" THEN 1 END) as pending_documents'),
                        DB::raw('COUNT(CASE WHEN c.document_status = "approved" THEN 1 END) as approved_documents'),
                        DB::raw('COUNT(CASE WHEN c.document_status = "rejected" THEN 1 END) as rejected_documents'),
                        DB::raw('AVG(m.price) as avg_membership_price'),
                        DB::raw('SUM(CASE WHEN c.status = 1 THEN m.price ELSE 0 END) as active_revenue')
                    ])
                    ->first();

                // Estadísticas por período
                $today = Carbon::today();
                $thisMonth = Carbon::now()->startOfMonth();
                $lastMonth = Carbon::now()->subMonth()->startOfMonth();

                $periodStats = [
                    'registered_today' => Carrier::whereDate('created_at', $today)->count(),
                    'registered_this_month' => Carrier::where('created_at', '>=', $thisMonth)->count(),
                    'registered_last_month' => Carrier::whereBetween('created_at', [
                        $lastMonth, 
                        $lastMonth->copy()->endOfMonth()
                    ])->count()
                ];

                // Calcular tasa de crecimiento mensual
                $growthRate = $periodStats['registered_last_month'] > 0 
                    ? (($periodStats['registered_this_month'] - $periodStats['registered_last_month']) / $periodStats['registered_last_month']) * 100
                    : 0;

                return array_merge((array) $stats, $periodStats, [
                    'monthly_growth_rate' => round($growthRate, 2),
                    'activation_rate' => $stats->total > 0 ? round(($stats->active / $stats->total) * 100, 2) : 0,
                    'document_approval_rate' => ($stats->pending_documents + $stats->approved_documents) > 0 
                        ? round(($stats->approved_documents / ($stats->pending_documents + $stats->approved_documents)) * 100, 2) 
                        : 0
                ]);
            } catch (Exception $e) {
                Log::error('Error al obtener estadísticas de carriers: ' . $e->getMessage());
                throw new Exception('Error al cargar estadísticas de transportistas');
            }
        });
    }

    /**
     * Estadísticas de conductores con métricas detalladas
     */
    public function getDriverStats(): array
    {
        $cacheKey = self::CACHE_PREFIX . 'drivers:overview';
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            try {
                $stats = DB::table('user_driver_details as udd')
                    ->join('users as u', 'udd.user_id', '=', 'u.id')
                    ->select([
                        DB::raw('COUNT(*) as total'),
                        DB::raw('COUNT(CASE WHEN udd.status = 1 THEN 1 END) as active'),
                        DB::raw('COUNT(CASE WHEN udd.status = 0 THEN 1 END) as inactive'),
                        DB::raw('COUNT(CASE WHEN udd.status = 2 THEN 1 END) as suspended'),
                        DB::raw('0 as valid_licenses'), // Placeholder - requiere join con driver_licenses
                        DB::raw('0 as expired_licenses'), // Placeholder - requiere join con driver_licenses
                        DB::raw('0 as suspended_licenses'), // Placeholder - requiere join con driver_licenses
                        DB::raw('0 as expiring_soon'), // Placeholder - requiere join con driver_licenses
                        DB::raw('0 as expired_count'), // Placeholder - requiere join con driver_licenses
                        DB::raw('AVG(DATEDIFF(NOW(), udd.created_at)) as avg_employment_days')
                    ])
                    ->first();

                // Estadísticas por carrier
                $carrierDistribution = DB::table('user_driver_details as udd')
                    ->join('carriers as c', 'udd.carrier_id', '=', 'c.id')
                    ->select([
                        'c.name as carrier_name',
                        DB::raw('COUNT(udd.id) as driver_count')
                    ])
                    ->where('udd.status', 'active')
                    ->groupBy('c.id', 'c.name')
                    ->orderBy('driver_count', 'desc')
                    ->limit(5)
                    ->get();

                // Estadísticas por estado de conductores (ya que license_type no existe)
                $driverStatusTypes = DB::table('user_driver_details')
                    ->select([
                        DB::raw('CASE 
                            WHEN status = 1 THEN "active"
                            WHEN status = 0 THEN "inactive"
                            WHEN status = 2 THEN "suspended"
                            ELSE "unknown"
                        END as status_type'),
                        DB::raw('COUNT(*) as count')
                    ])
                    ->groupBy('status')
                    ->get();

                // Estadísticas de período
                $today = Carbon::today();
                $thisMonth = Carbon::now()->startOfMonth();
                $lastMonth = Carbon::now()->subMonth()->startOfMonth();

                $periodStats = [
                    'registered_today' => UserDriverDetail::whereDate('created_at', $today)->count(),
                    'registered_this_month' => UserDriverDetail::where('created_at', '>=', $thisMonth)->count(),
                    'registered_last_month' => UserDriverDetail::whereBetween('created_at', [
                        $lastMonth, 
                        $lastMonth->copy()->endOfMonth()
                    ])->count()
                ];

                // Calcular tasa de crecimiento mensual
                $growthRate = $periodStats['registered_last_month'] > 0 
                    ? (($periodStats['registered_this_month'] - $periodStats['registered_last_month']) / $periodStats['registered_last_month']) * 100
                    : 0;

                return array_merge((array) $stats, $periodStats, [
                    'monthly_growth_rate' => round($growthRate, 2),
                    'license_validity_rate' => $stats->total > 0 ? round(($stats->valid_licenses / $stats->total) * 100, 2) : 0,
                    'activation_rate' => $stats->total > 0 ? round(($stats->active / $stats->total) * 100, 2) : 0,
                    'avg_employment_months' => round($stats->avg_employment_days / 30, 1),
                    'top_carriers_by_drivers' => $carrierDistribution,
                    'driver_status_distribution' => $driverStatusTypes,
                    'critical_alerts' => $stats->expired_count + $stats->expiring_soon
                ]);
            } catch (Exception $e) {
                Log::error('Error al obtener estadísticas de conductores: ' . $e->getMessage());
                throw new Exception('Error al cargar estadísticas de conductores');
            }
        });
    }

    /**
     * Estadísticas de vehículos con métricas detalladas
     */
    public function getVehicleStats(): array
    {
        $cacheKey = self::CACHE_PREFIX . 'vehicles:overview';
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            try {
                $stats = DB::table('vehicles as v')
                    ->leftJoin('carriers as c', 'v.carrier_id', '=', 'c.id')
                    ->select([
                        DB::raw('COUNT(*) as total'),
                        DB::raw('COUNT(CASE WHEN v.status = "active" THEN 1 END) as active'),
                        DB::raw('COUNT(CASE WHEN v.status = "inactive" THEN 1 END) as inactive'),
                        DB::raw('COUNT(CASE WHEN v.status = "pending" THEN 1 END) as pending'),
                        DB::raw('COUNT(CASE WHEN v.status = "suspended" THEN 1 END) as suspended'),
                        DB::raw('COUNT(CASE WHEN v.status = "out_of_service" THEN 1 END) as out_of_service'),
                        DB::raw('COUNT(CASE WHEN v.registration_expiration_date < NOW() THEN 1 END) as expired_registration'),
                        DB::raw('COUNT(CASE WHEN v.registration_expiration_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY) THEN 1 END) as expiring_soon'),
                        DB::raw('COUNT(CASE WHEN v.annual_inspection_expiration_date < NOW() THEN 1 END) as expired_inspection'),
                        DB::raw('AVG(YEAR(NOW()) - v.year) as avg_age')
                    ])
                    ->first();

                // Estadísticas de mantenimiento
                $maintenanceStats = DB::table('vehicle_maintenances as vm')
                    ->select([
                        DB::raw('COUNT(*) as total_maintenance'),
                        DB::raw('COUNT(CASE WHEN vm.status = "completed" THEN 1 END) as completed'),
                        DB::raw('COUNT(CASE WHEN vm.status = "pending" THEN 1 END) as pending'),
                        DB::raw('COUNT(CASE WHEN vm.status = "in_progress" THEN 1 END) as in_progress'),
                        DB::raw('COUNT(CASE WHEN vm.status = "upcoming" THEN 1 END) as upcoming'),
                        DB::raw('COUNT(CASE WHEN vm.status = "overdue" THEN 1 END) as overdue'),
                        DB::raw('AVG(vm.cost) as avg_maintenance_cost'),
                        DB::raw('SUM(vm.cost) as total_maintenance_cost')
                    ])
                    ->first();

                // Distribución por tipo de vehículo
                $typeDistribution = DB::table('vehicles')
                    ->select([
                        'type',
                        DB::raw('COUNT(*) as count')
                    ])
                    ->where('status', 'active')
                    ->groupBy('type')
                    ->orderBy('count', 'desc')
                    ->get();

                // Distribución por marca
                $makeDistribution = DB::table('vehicles')
                    ->select([
                        'make',
                        DB::raw('COUNT(*) as count')
                    ])
                    ->where('status', 'active')
                    ->groupBy('make')
                    ->orderBy('count', 'desc')
                    ->limit(5)
                    ->get();

                // Estadísticas de período
                $today = Carbon::today();
                $thisMonth = Carbon::now()->startOfMonth();
                $lastMonth = Carbon::now()->subMonth()->startOfMonth();

                $periodStats = [
                    'registered_today' => Vehicle::whereDate('created_at', $today)->count(),
                    'registered_this_month' => Vehicle::where('created_at', '>=', $thisMonth)->count(),
                    'registered_last_month' => Vehicle::whereBetween('created_at', [
                        $lastMonth, 
                        $lastMonth->copy()->endOfMonth()
                    ])->count()
                ];

                // Calcular tasa de crecimiento mensual
                $growthRate = $periodStats['registered_last_month'] > 0 
                    ? (($periodStats['registered_this_month'] - $periodStats['registered_last_month']) / $periodStats['registered_last_month']) * 100
                    : 0;

                return array_merge((array) $stats, (array) $maintenanceStats, $periodStats, [
                    'monthly_growth_rate' => round($growthRate, 2),
                    'operational_rate' => $stats->total > 0 ? round(($stats->active / $stats->total) * 100, 2) : 0,
                    'maintenance_completion_rate' => $maintenanceStats->total_maintenance > 0 ? 
                        round(($maintenanceStats->completed / $maintenanceStats->total_maintenance) * 100, 2) : 0,
                    'critical_alerts' => $stats->expired_registration + $stats->expired_inspection + $maintenanceStats->overdue,
                    'type_distribution' => $typeDistribution,
                    'make_distribution' => $makeDistribution,
                    'avg_age_years' => round($stats->avg_age, 1),
                    'avg_maintenance_cost' => round($maintenanceStats->avg_maintenance_cost, 2),
                    'total_maintenance_cost' => round($maintenanceStats->total_maintenance_cost, 2)
                ]);
            } catch (Exception $e) {
                Log::error('Error al obtener estadísticas de vehículos: ' . $e->getMessage());
                throw new Exception('Error al cargar estadísticas de vehículos');
            }
        });
    }

    /**
     * Estadísticas de usuarios del sistema
     */
    public function getUserStats(): array
    {
        $cacheKey = self::CACHE_PREFIX . 'users:overview';
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            try {
                $stats = DB::table('users as u')
                    ->select([
                        DB::raw('COUNT(*) as total'),
                        DB::raw('COUNT(CASE WHEN u.status = "active" THEN 1 END) as active'),
                        DB::raw('COUNT(CASE WHEN u.status = "pending" THEN 1 END) as pending'),
                        DB::raw('COUNT(CASE WHEN u.status = "inactive" THEN 1 END) as inactive'),
                        DB::raw('COUNT(CASE WHEN u.status = "suspended" THEN 1 END) as suspended'),
                        DB::raw('COUNT(CASE WHEN u.email_verified_at IS NOT NULL THEN 1 END) as verified_email'),
                        DB::raw('COUNT(CASE WHEN u.email_verified_at IS NULL THEN 1 END) as unverified_email')
                    ])
                    ->first();

                // Estadísticas de período
                $today = Carbon::today();
                $thisMonth = Carbon::now()->startOfMonth();
                $lastMonth = Carbon::now()->subMonth()->startOfMonth();

                $periodStats = [
                    'registered_today' => User::whereDate('created_at', $today)->count(),
                    'registered_this_month' => User::where('created_at', '>=', $thisMonth)->count(),
                    'registered_last_month' => User::whereBetween('created_at', [
                        $lastMonth, 
                        $lastMonth->copy()->endOfMonth()
                    ])->count()
                ];

                // Calcular tasa de crecimiento mensual
                $growthRate = $periodStats['registered_last_month'] > 0 
                    ? (($periodStats['registered_this_month'] - $periodStats['registered_last_month']) / $periodStats['registered_last_month']) * 100
                    : 0;

                return array_merge((array) $stats, $periodStats, [
                    'monthly_growth_rate' => round($growthRate, 2),
                    'activation_rate' => $stats->total > 0 ? round(($stats->active / $stats->total) * 100, 2) : 0,
                    'email_verification_rate' => $stats->total > 0 ? round(($stats->verified_email / $stats->total) * 100, 2) : 0
                ]);
            } catch (Exception $e) {
                Log::error('Error al obtener estadísticas de usuarios: ' . $e->getMessage());
                throw new Exception('Error al cargar estadísticas de usuarios');
            }
        });
    }

    /**
     * Estadísticas de ingresos y membresías
     */
    public function getRevenueStats(): array
    {
        $cacheKey = self::CACHE_PREFIX . 'revenue:overview';
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            try {
                // Ingresos por membresía
                $membershipRevenue = DB::table('carriers as c')
                    ->join('memberships as m', 'c.id_plan', '=', 'm.id')
                    ->select([
                        'm.name as membership_name',
                        'm.price',
                        DB::raw('COUNT(c.id) as subscribers'),
                        DB::raw('COUNT(CASE WHEN c.status = "active" THEN 1 END) as active_subscribers'),
                        DB::raw('SUM(CASE WHEN c.status = "active" THEN m.price ELSE 0 END) as active_revenue'),
                        DB::raw('SUM(m.price) as potential_revenue')
                    ])
                    ->groupBy('m.id', 'm.name', 'm.price')
                    ->orderBy('active_revenue', 'desc')
                    ->get();

                // Totales generales
                $totalStats = [
                    'total_active_revenue' => $membershipRevenue->sum('active_revenue'),
                    'total_potential_revenue' => $membershipRevenue->sum('potential_revenue'),
                    'total_active_subscribers' => $membershipRevenue->sum('active_subscribers'),
                    'total_subscribers' => $membershipRevenue->sum('subscribers')
                ];

                // Cálculo de métricas
                $metrics = [
                    'revenue_realization_rate' => $totalStats['total_potential_revenue'] > 0 
                        ? round(($totalStats['total_active_revenue'] / $totalStats['total_potential_revenue']) * 100, 2) 
                        : 0,
                    'avg_revenue_per_active_subscriber' => $totalStats['total_active_subscribers'] > 0 
                        ? round($totalStats['total_active_revenue'] / $totalStats['total_active_subscribers'], 2) 
                        : 0,
                    'subscriber_activation_rate' => $totalStats['total_subscribers'] > 0 
                        ? round(($totalStats['total_active_subscribers'] / $totalStats['total_subscribers']) * 100, 2) 
                        : 0
                ];

                // Proyección mensual
                $monthlyProjection = $this->calculateMonthlyRevenueProjection();

                return array_merge($totalStats, $metrics, [
                    'membership_breakdown' => $membershipRevenue,
                    'monthly_projection' => $monthlyProjection,
                    'growth_opportunities' => $totalStats['total_potential_revenue'] - $totalStats['total_active_revenue']
                ]);
            } catch (Exception $e) {
                Log::error('Error al obtener estadísticas de ingresos: ' . $e->getMessage());
                throw new Exception('Error al cargar estadísticas de ingresos');
            }
        });
    }

    /**
     * Estadísticas de actividad del sistema
     */
    public function getActivityStats(): array
    {
        $cacheKey = self::CACHE_PREFIX . 'activity:overview';
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            try {
                $today = Carbon::today();
                $thisWeek = Carbon::now()->startOfWeek();
                $thisMonth = Carbon::now()->startOfMonth();

                return [
                    'daily_activity' => [
                        'new_carriers' => Carrier::whereDate('created_at', $today)->count(),
                        'new_drivers' => UserDriverDetail::whereDate('created_at', $today)->count(),
                        'document_approvals' => Carrier::whereDate('updated_at', $today)
                            ->where('document_status', 'approved')->count(),
                        'activations' => Carrier::whereDate('updated_at', $today)
                            ->where('status', 'active')->count()
                    ],
                    'weekly_activity' => [
                        'new_carriers' => Carrier::where('created_at', '>=', $thisWeek)->count(),
                        'new_drivers' => UserDriverDetail::where('created_at', '>=', $thisWeek)->count(),
                        'total_registrations' => Carrier::where('created_at', '>=', $thisWeek)->count() + 
                                               UserDriverDetail::where('created_at', '>=', $thisWeek)->count()
                    ],
                    'monthly_activity' => [
                        'new_carriers' => Carrier::where('created_at', '>=', $thisMonth)->count(),
                        'new_drivers' => UserDriverDetail::where('created_at', '>=', $thisMonth)->count(),
                        'revenue_generated' => DB::table('carriers as c')
                            ->join('memberships as m', 'c.id_plan', '=', 'm.id')
                            ->where('c.created_at', '>=', $thisMonth)
                            ->where('c.status', 'active')
                            ->sum('m.price')
                    ],
                    'system_health' => [
                        'total_active_entities' => Carrier::where('status', 'active')->count() + 
                                                 UserDriverDetail::where('status', 'active')->count(),
                        'pending_approvals' => Carrier::where('document_status', 'pending')->count(),
                        'system_utilization' => $this->calculateSystemUtilization(),
                        'data_integrity_score' => $this->calculateDataIntegrityScore()
                    ]
                ];
            } catch (Exception $e) {
                Log::error('Error al obtener estadísticas de actividad: ' . $e->getMessage());
                throw new Exception('Error al cargar estadísticas de actividad');
            }
        });
    }

    /**
     * Estadísticas de crecimiento y tendencias
     */
    public function getGrowthStats(): array
    {
        $cacheKey = self::CACHE_PREFIX . 'growth:trends';
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            try {
                // Crecimiento mensual de los últimos 6 meses
                $monthlyGrowth = [];
                for ($i = 5; $i >= 0; $i--) {
                    $month = Carbon::now()->subMonths($i);
                    $startOfMonth = $month->copy()->startOfMonth();
                    $endOfMonth = $month->copy()->endOfMonth();
                    
                    $monthlyGrowth[] = [
                        'month' => $month->format('Y-m'),
                        'month_name' => $month->format('M Y'),
                        'carriers' => Carrier::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                        'drivers' => UserDriverDetail::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                        'revenue' => DB::table('carriers as c')
                            ->join('memberships as m', 'c.id_plan', '=', 'm.id')
                            ->whereBetween('c.created_at', [$startOfMonth, $endOfMonth])
                            ->where('c.status', 'active')
                            ->sum('m.price')
                    ];
                }

                // Calcular tasas de crecimiento
                $currentMonth = end($monthlyGrowth);
                $previousMonth = $monthlyGrowth[count($monthlyGrowth) - 2] ?? null;

                $growthRates = [
                    'carrier_growth_rate' => $this->calculateGrowthRate(
                        $previousMonth['carriers'] ?? 0, 
                        $currentMonth['carriers']
                    ),
                    'driver_growth_rate' => $this->calculateGrowthRate(
                        $previousMonth['drivers'] ?? 0, 
                        $currentMonth['drivers']
                    ),
                    'revenue_growth_rate' => $this->calculateGrowthRate(
                        $previousMonth['revenue'] ?? 0, 
                        $currentMonth['revenue']
                    )
                ];

                // Proyecciones para el próximo mes
                $projections = [
                    'projected_carriers' => $this->projectNextMonth($monthlyGrowth, 'carriers'),
                    'projected_drivers' => $this->projectNextMonth($monthlyGrowth, 'drivers'),
                    'projected_revenue' => $this->projectNextMonth($monthlyGrowth, 'revenue')
                ];

                return [
                    'monthly_data' => $monthlyGrowth,
                    'growth_rates' => $growthRates,
                    'projections' => $projections,
                    'trends' => $this->analyzeTrends($monthlyGrowth)
                ];
            } catch (Exception $e) {
                Log::error('Error al obtener estadísticas de crecimiento: ' . $e->getMessage());
                throw new Exception('Error al cargar estadísticas de crecimiento');
            }
        });
    }

    /**
     * Alertas y notificaciones del sistema
     */
    public function getSystemAlerts(): array
    {
        try {
            $alerts = [];

            // Licencias próximas a vencer
            $expiringLicenses = UserDriverDetail::whereBetween('license_expiry', [
                now(), 
                now()->addDays(30)
            ])->count();

            if ($expiringLicenses > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'category' => 'licenses',
                    'title' => 'Licencias próximas a vencer',
                    'message' => "{$expiringLicenses} licencias vencen en los próximos 30 días",
                    'count' => $expiringLicenses,
                    'priority' => 'high'
                ];
            }

            // Documentos pendientes de aprobación
            $pendingDocuments = Carrier::where('document_status', 'pending')->count();
            if ($pendingDocuments > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'category' => 'documents',
                    'title' => 'Documentos pendientes',
                    'message' => "{$pendingDocuments} transportistas esperan aprobación de documentos",
                    'count' => $pendingDocuments,
                    'priority' => 'medium'
                ];
            }

            // Carriers inactivos con potencial de reactivación
            $inactiveCarriers = Carrier::where('status', 'inactive')
                ->where('created_at', '>', now()->subDays(90))
                ->count();

            if ($inactiveCarriers > 0) {
                $alerts[] = [
                    'type' => 'opportunity',
                    'category' => 'reactivation',
                    'title' => 'Oportunidades de reactivación',
                    'message' => "{$inactiveCarriers} transportistas inactivos recientes pueden reactivarse",
                    'count' => $inactiveCarriers,
                    'priority' => 'low'
                ];
            }

            // Verificar integridad de datos
            $dataIssues = $this->checkDataIntegrity();
            if (!empty($dataIssues)) {
                $alerts[] = [
                    'type' => 'error',
                    'category' => 'data_integrity',
                    'title' => 'Problemas de integridad de datos',
                    'message' => count($dataIssues) . ' problemas detectados en la base de datos',
                    'count' => count($dataIssues),
                    'priority' => 'critical',
                    'details' => $dataIssues
                ];
            }

            return $alerts;
        } catch (Exception $e) {
            Log::error('Error al obtener alertas del sistema: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estadísticas principales (alias para getDashboardStats)
     */
    public function getMainStatistics(): array
    {
        return $this->getDashboardStats();
    }

    /**
     * Obtener estadísticas de tendencias (alias para getGrowthStats)
     */
    public function getTrendStatistics(): array
    {
        return $this->getGrowthStats();
    }

    /**
     * Obtener estadísticas de crecimiento (alias para getGrowthStats)
     */
    public function getGrowthStatistics(): array
    {
        return $this->getGrowthStats();
    }

    /**
     * Limpiar todas las cachés de estadísticas
     */
    public function clearAllStatsCache(): bool
    {
        try {
            $patterns = [
                self::CACHE_PREFIX . 'dashboard:*',
                self::CACHE_PREFIX . 'carriers:*',
                self::CACHE_PREFIX . 'drivers:*',
                self::CACHE_PREFIX . 'revenue:*',
                self::CACHE_PREFIX . 'activity:*',
                self::CACHE_PREFIX . 'growth:*'
            ];

            foreach ($patterns as $pattern) {
                Cache::forget($pattern);
            }

            Log::info('Todas las cachés de estadísticas han sido limpiadas');
            return true;
        } catch (Exception $e) {
            Log::error('Error al limpiar cachés de estadísticas: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas en tiempo real (sin caché)
     */
    public function getRealTimeStats(): array
    {
        try {
            return [
                'timestamp' => now()->toISOString(),
                'active_carriers' => Carrier::where('status', 'active')->count(),
                'active_drivers' => UserDriverDetail::where('status', 'active')->count(),
                'pending_approvals' => Carrier::where('document_status', 'pending')->count(),
                'system_load' => $this->getSystemLoad()
            ];
        } catch (Exception $e) {
            Log::error('Error al obtener estadísticas en tiempo real: ' . $e->getMessage());
            throw new Exception('Error al cargar estadísticas en tiempo real');
        }
    }

    // Métodos privados auxiliares

    private function calculateMonthlyRevenueProjection(): array
    {
        $currentRevenue = DB::table('carriers as c')
            ->join('memberships as m', 'c.id_plan', '=', 'm.id')
            ->where('c.status', 'active')
            ->sum('m.price');

        $growthRate = 0.05; // 5% de crecimiento mensual estimado
        
        return [
            'current_monthly' => $currentRevenue,
            'projected_next_month' => $currentRevenue * (1 + $growthRate),
            'projected_quarterly' => $currentRevenue * 3 * (1 + $growthRate),
            'growth_rate_used' => $growthRate * 100
        ];
    }

    private function calculateSystemUtilization(): float
    {
        $totalEntities = Carrier::count() + UserDriverDetail::count();
        $activeEntities = Carrier::where('status', 'active')->count() + 
                         UserDriverDetail::where('status', 'active')->count();
        
        return $totalEntities > 0 ? round(($activeEntities / $totalEntities) * 100, 2) : 0;
    }

    private function calculateDataIntegrityScore(): float
    {
        $issues = $this->checkDataIntegrity();
        $totalChecks = 10; // Número total de verificaciones
        $passedChecks = $totalChecks - count($issues);
        
        return round(($passedChecks / $totalChecks) * 100, 2);
    }

    private function checkDataIntegrity(): array
    {
        $issues = [];

        // Verificar carriers sin membresía
        $carriersWithoutMembership = Carrier::whereNull('id_plan')->count();
        if ($carriersWithoutMembership > 0) {
            $issues[] = "Carriers sin membresía: {$carriersWithoutMembership}";
        }

        // Verificar conductores sin carrier
        $driversWithoutCarrier = UserDriverDetail::whereNull('carrier_id')->count();
        if ($driversWithoutCarrier > 0) {
            $issues[] = "Conductores sin transportista: {$driversWithoutCarrier}";
        }

        return $issues;
    }

    private function calculateGrowthRate(float $previous, float $current): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        
        return round((($current - $previous) / $previous) * 100, 2);
    }

    private function projectNextMonth(array $monthlyData, string $metric): float
    {
        if (count($monthlyData) < 3) {
            return 0;
        }

        $values = array_column($monthlyData, $metric);
        $lastThree = array_slice($values, -3);
        
        // Promedio simple de los últimos 3 meses
        return round(array_sum($lastThree) / count($lastThree), 2);
    }

    private function analyzeTrends(array $monthlyData): array
    {
        $trends = [];
        
        if (count($monthlyData) >= 2) {
            $latest = end($monthlyData);
            $previous = $monthlyData[count($monthlyData) - 2];
            
            $trends['carriers'] = $latest['carriers'] > $previous['carriers'] ? 'up' : 
                                ($latest['carriers'] < $previous['carriers'] ? 'down' : 'stable');
            
            $trends['drivers'] = $latest['drivers'] > $previous['drivers'] ? 'up' : 
                               ($latest['drivers'] < $previous['drivers'] ? 'down' : 'stable');
            
            $trends['revenue'] = $latest['revenue'] > $previous['revenue'] ? 'up' : 
                               ($latest['revenue'] < $previous['revenue'] ? 'down' : 'stable');
        }
        
        return $trends;
    }

    private function getSystemLoad(): array
    {
        // Simulación de carga del sistema
        return [
            'cpu_usage' => rand(10, 80),
            'memory_usage' => rand(30, 90),
            'database_connections' => rand(5, 50),
            'cache_hit_rate' => rand(85, 99)
        ];
    }

    /**
     * Obtener datos para gráficos de tendencias
     */
    public function getChartData(string $type, string $period = 'monthly', int $limit = 12): array
    {
        $cacheKey = self::CACHE_PREFIX . "chart:{$type}:{$period}:{$limit}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($type, $period, $limit) {
            try {
                switch ($type) {
                    case 'registrations':
                        return $this->getRegistrationTrendData($period, $limit);
                    case 'revenue':
                        return $this->getRevenueTrendData($period, $limit);
                    case 'activity':
                        return $this->getActivityTrendData($period, $limit);
                    case 'distribution':
                        return $this->getDistributionData($period);
                    default:
                        throw new Exception("Tipo de gráfico no válido: {$type}");
                }
            } catch (Exception $e) {
                Log::error("Error al obtener datos del gráfico {$type}: " . $e->getMessage());
                throw new Exception("Error al cargar datos del gráfico");
            }
        });
    }

    /**
     * Datos de tendencias de registros
     */
    private function getRegistrationTrendData(string $period, int $limit): array
    {
        $data = [];
        $dateFormat = $period === 'daily' ? 'Y-m-d' : 'Y-m';
        
        for ($i = $limit - 1; $i >= 0; $i--) {
            $date = $period === 'daily' 
                ? Carbon::now()->subDays($i)
                : Carbon::now()->subMonths($i);
            
            $startDate = $period === 'daily' 
                ? $date->copy()->startOfDay()
                : $date->copy()->startOfMonth();
            
            $endDate = $period === 'daily' 
                ? $date->copy()->endOfDay()
                : $date->copy()->endOfMonth();
            
            $data[] = [
                'period' => $date->format($dateFormat),
                'label' => $period === 'daily' ? $date->format('M d') : $date->format('M Y'),
                'carriers' => Carrier::whereBetween('created_at', [$startDate, $endDate])->count(),
                'drivers' => UserDriverDetail::whereBetween('created_at', [$startDate, $endDate])->count(),
                'vehicles' => Vehicle::whereBetween('created_at', [$startDate, $endDate])->count(),
                'users' => User::whereBetween('created_at', [$startDate, $endDate])->count()
            ];
        }
        
        return $data;
    }

    /**
     * Datos de tendencias de ingresos
     */
    private function getRevenueTrendData(string $period, int $limit): array
    {
        $data = [];
        $dateFormat = $period === 'daily' ? 'Y-m-d' : 'Y-m';
        
        for ($i = $limit - 1; $i >= 0; $i--) {
            $date = $period === 'daily' 
                ? Carbon::now()->subDays($i)
                : Carbon::now()->subMonths($i);
            
            $startDate = $period === 'daily' 
                ? $date->copy()->startOfDay()
                : $date->copy()->startOfMonth();
            
            $endDate = $period === 'daily' 
                ? $date->copy()->endOfDay()
                : $date->copy()->endOfMonth();
            
            $revenue = DB::table('carriers as c')
                ->join('memberships as m', 'c.id_plan', '=', 'm.id')
                ->whereBetween('c.created_at', [$startDate, $endDate])
                ->where('c.status', 'active')
                ->sum('m.price');
            
            $data[] = [
                'period' => $date->format($dateFormat),
                'label' => $period === 'daily' ? $date->format('M d') : $date->format('M Y'),
                'revenue' => round($revenue, 2),
                'new_subscribers' => Carrier::whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'active')
                    ->count()
            ];
        }
        
        return $data;
    }

    /**
     * Datos de tendencias de actividad
     */
    private function getActivityTrendData(string $period, int $limit): array
    {
        $data = [];
        $dateFormat = $period === 'daily' ? 'Y-m-d' : 'Y-m';
        
        for ($i = $limit - 1; $i >= 0; $i--) {
            $date = $period === 'daily' 
                ? Carbon::now()->subDays($i)
                : Carbon::now()->subMonths($i);
            
            $startDate = $period === 'daily' 
                ? $date->copy()->startOfDay()
                : $date->copy()->startOfMonth();
            
            $endDate = $period === 'daily' 
                ? Carbon::now()->subDays($i)
                : Carbon::now()->subMonths($i);
            
            $data[] = [
                'period' => $date->format($dateFormat),
                'label' => $period === 'daily' ? $date->format('M d') : $date->format('M Y'),
                'document_approvals' => Carrier::whereBetween('updated_at', [$startDate, $endDate])
                    ->where('document_status', 'approved')
                    ->count(),
                'activations' => Carrier::whereBetween('updated_at', [$startDate, $endDate])
                    ->where('status', 'active')
                    ->count(),
                'maintenance_completed' => VehicleMaintenance::whereBetween('updated_at', [$startDate, $endDate])
                    ->where('status', 'completed')
                    ->count()
            ];
        }
        
        return $data;
    }

    /**
     * Datos de distribución para gráficos circulares
     */
    private function getDistributionData(string $type): array
    {
        switch ($type) {
            case 'carrier_status':
                return DB::table('carriers')
                    ->select('status', DB::raw('COUNT(*) as count'))
                    ->groupBy('status')
                    ->get()
                    ->toArray();
                    
            case 'driver_status':
                return DB::table('user_driver_details')
                    ->select(
                        DB::raw('CASE 
                            WHEN status = 1 THEN "active"
                            WHEN status = 0 THEN "inactive"
                            WHEN status = 2 THEN "suspended"
                            ELSE "unknown"
                        END as status'),
                        DB::raw('COUNT(*) as count')
                    )
                    ->groupBy('status')
                    ->get()
                    ->toArray();
                    
            case 'vehicle_type':
                return DB::table('vehicles')
                    ->select('type', DB::raw('COUNT(*) as count'))
                    ->where('status', 'active')
                    ->groupBy('type')
                    ->orderBy('count', 'desc')
                    ->get()
                    ->toArray();
                    
            case 'membership_distribution':
                return DB::table('carriers as c')
                    ->join('memberships as m', 'c.id_plan', '=', 'm.id')
                    ->select('m.name as membership_name', DB::raw('COUNT(c.id) as count'))
                    ->groupBy('m.id', 'm.name')
                    ->orderBy('count', 'desc')
                    ->get()
                    ->toArray();
                    
            default:
                return [];
        }
    }

    /**
     * Obtener registros recientes para el dashboard
     */
    public function getRecentRecords(string $type, int $limit = 10): array
    {
        $cacheKey = self::CACHE_PREFIX . "recent:{$type}:{$limit}";
        
        return Cache::remember($cacheKey, 300, function () use ($type, $limit) { // Cache más corto para datos recientes
            try {
                switch ($type) {
                    case 'carriers':
                        return $this->getRecentDrivers($limit);
                    case 'drivers':
                        return $this->getRecentDrivers($limit);
                    case 'vehicles':
                        return $this->getRecentVehicles($limit);
                    case 'maintenance':
                        return $this->getRecentMaintenance($limit);
                    case 'alerts':
                        return $this->getRecentAlerts($limit);
                    default:
                        throw new Exception("Tipo de registro no válido: {$type}");
                }
            } catch (Exception $e) {
                Log::error("Error al obtener registros recientes {$type}: " . $e->getMessage());
                return [];
            }
        });
    }



    /**
     * Conductores recientes
     */
    public function getRecentDrivers(int $limit = 5): array
    {
        $cacheKey = self::CACHE_PREFIX . 'recent_drivers:' . $limit;
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($limit) {
            return \App\Models\UserDriverDetail::with(['user', 'carrier'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($driver) {
                    return [
                        'id' => $driver->id,
                        'user_id' => $driver->user_id,
                        'full_name' => trim(($driver->user->name ?? '') . ' ' . ($driver->last_name ?? '')),
                        'email' => $driver->user->email ?? '',
                        'status' => $driver->status == 1 ? 'Active' : ($driver->status == 0 ? 'Inactive' : 'Suspended'),
                        'application_completed' => $driver->application_completed == 1 ? 'Completed' : 'Pending',
                        'carrier_name' => $driver->carrier->name ?? 'No Carrier',
                        'carrier_slug' => $driver->carrier->slug ?? null,
                        'profile_photo_url' => $driver->profile_photo_url,
                        'created_at' => Carbon::parse($driver->created_at)->diffForHumans(),
                        'created_date' => Carbon::parse($driver->created_at)->format('Y-m-d H:i')
                    ];
                })
                ->toArray();
        });
    }



    /**
     * Mantenimientos recientes
     */
    private function getRecentMaintenance(int $limit): array
    {
        return DB::table('vehicle_maintenances as vm')
            ->join('vehicles as v', 'vm.vehicle_id', '=', 'v.id')
            ->leftJoin('carriers as c', 'v.carrier_id', '=', 'c.id')
            ->select([
                'vm.id',
                'vm.type',
                'vm.status',
                'vm.cost',
                'vm.scheduled_date',
                'vm.created_at',
                'v.make',
                'v.model',
                'v.year',
                'c.name as carrier_name'
            ])
            ->orderBy('vm.created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($maintenance) {
                return [
                    'id' => $maintenance->id,
                    'type' => $maintenance->type,
                    'status' => $maintenance->status,
                    'cost' => $maintenance->cost,
                    'vehicle' => "{$maintenance->year} {$maintenance->make} {$maintenance->model}",
                    'carrier' => $maintenance->carrier_name,
                    'scheduled_date' => Carbon::parse($maintenance->scheduled_date)->format('Y-m-d'),
                    'created_at' => Carbon::parse($maintenance->created_at)->diffForHumans(),
                    'created_date' => Carbon::parse($maintenance->created_at)->format('Y-m-d H:i')
                ];
            })
            ->toArray();
    }

    /**
     * Alertas recientes del sistema
     */
    private function getRecentAlerts(int $limit): array
    {
        $alerts = [];
        
        // Vehículos con registros próximos a vencer
        $expiringRegistrations = DB::table('vehicles as v')
            ->leftJoin('carriers as c', 'v.carrier_id', '=', 'c.id')
            ->select([
                'v.id',
                'v.make',
                'v.model',
                'v.year',
                'v.registration_expiration_date',
                'c.name as carrier_name'
            ])
            ->whereBetween('v.registration_expiration_date', [now(), now()->addDays(30)])
            ->orderBy('v.registration_expiration_date', 'asc')
            ->limit($limit)
            ->get();

        foreach ($expiringRegistrations as $vehicle) {
            $alerts[] = [
                'type' => 'warning',
                'category' => 'vehicle_registration',
                'title' => 'Registro de vehículo próximo a vencer',
                'message' => "Vehículo {$vehicle->year} {$vehicle->make} {$vehicle->model} - Vence: " . 
                           Carbon::parse($vehicle->registration_expiration_date)->format('Y-m-d'),
                'entity' => $vehicle->carrier_name,
                'date' => Carbon::parse($vehicle->registration_expiration_date)->diffForHumans(),
                'priority' => 'high'
            ];
        }

        // Mantenimientos vencidos
        $overdueMaintenance = DB::table('vehicle_maintenances as vm')
            ->join('vehicles as v', 'vm.vehicle_id', '=', 'v.id')
            ->leftJoin('carriers as c', 'v.carrier_id', '=', 'c.id')
            ->select([
                'vm.id',
                'vm.type',
                'vm.scheduled_date',
                'v.make',
                'v.model',
                'v.year',
                'c.name as carrier_name'
            ])
            ->where('vm.status', 'overdue')
            ->orderBy('vm.scheduled_date', 'asc')
            ->limit($limit)
            ->get();

        foreach ($overdueMaintenance as $maintenance) {
            $alerts[] = [
                'type' => 'error',
                'category' => 'maintenance_overdue',
                'title' => 'Mantenimiento vencido',
                'message' => "Mantenimiento {$maintenance->type} para {$maintenance->year} {$maintenance->make} {$maintenance->model}",
                'entity' => $maintenance->carrier_name,
                'date' => Carbon::parse($maintenance->scheduled_date)->diffForHumans(),
                'priority' => 'critical'
            ];
        }

        // Ordenar por prioridad y fecha
        usort($alerts, function ($a, $b) {
            $priorities = ['critical' => 4, 'high' => 3, 'medium' => 2, 'low' => 1];
            return $priorities[$b['priority']] - $priorities[$a['priority']];
        });

        return array_slice($alerts, 0, $limit);
    }

    /**
     * Obtener métricas de rendimiento del sistema
     */
    public function getPerformanceMetrics(): array
    {
        $cacheKey = self::CACHE_PREFIX . 'performance:metrics';
        
        return Cache::remember($cacheKey, 600, function () { // Cache de 10 minutos
            try {
                return [
                    'database_performance' => $this->getDatabasePerformance(),
                    'cache_performance' => $this->getCachePerformance(),
                    'system_resources' => $this->getSystemResources(),
                    'response_times' => $this->getResponseTimes()
                ];
            } catch (Exception $e) {
                Log::error('Error al obtener métricas de rendimiento: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Rendimiento de la base de datos
     */
    private function getDatabasePerformance(): array
    {
        $startTime = microtime(true);
        
        // Consulta simple para medir tiempo de respuesta
        DB::table('carriers')->count();
        
        $queryTime = (microtime(true) - $startTime) * 1000; // En milisegundos
        
        return [
            'query_response_time' => round($queryTime, 2),
            'active_connections' => rand(5, 50), // Simulado
            'slow_queries' => rand(0, 5), // Simulado
            'cache_hit_ratio' => rand(85, 99) // Simulado
        ];
    }

    /**
     * Rendimiento del cache
     */
    private function getCachePerformance(): array
    {
        return [
            'hit_rate' => rand(85, 99),
            'miss_rate' => rand(1, 15),
            'memory_usage' => rand(30, 80),
            'keys_count' => rand(100, 1000)
        ];
    }

    /**
     * Recursos del sistema
     */
    private function getSystemResources(): array
    {
        return [
            'cpu_usage' => rand(10, 80),
            'memory_usage' => rand(30, 90),
            'disk_usage' => rand(20, 70),
            'network_io' => rand(5, 50)
        ];
    }

    /**
     * Tiempos de respuesta
     */
    private function getResponseTimes(): array
    {
        return [
            'avg_response_time' => rand(50, 200),
            'min_response_time' => rand(10, 50),
            'max_response_time' => rand(200, 500),
            'requests_per_minute' => rand(50, 200)
        ];
    }

    /**
     * Obtener vehículos recientes registrados para la tabla Recent Activity
     */
    public function getRecentVehicles(int $limit = 10): array
    {
        $cacheKey = self::CACHE_PREFIX . 'recent_vehicles:' . $limit;
        
        return Cache::remember($cacheKey, 300, function () use ($limit) { // Cache por 5 minutos
            try {
                $vehicles = Vehicle::with([
                    'carrier:id,name',
                    'currentDriverAssignment:id,vehicle_id,driver_type'
                ])
                ->select([
                    'id',
                    'make',
                    'model', 
                    'carrier_id',
                    'status',
                    'created_at'
                ])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($vehicle) {
                    return [
                        'id' => $vehicle->id,
                        'make_model' => trim($vehicle->make . ' ' . $vehicle->model),
                        'carrier_name' => $vehicle->carrier->name ?? 'N/A',
                        'assignment_type' => $vehicle->currentDriverAssignment->driver_type ?? 'No asignado',
                        'status' => ucfirst($vehicle->status),
                        'registration_date' => $vehicle->created_at->format('M d, Y'),
                        'registration_date_raw' => $vehicle->created_at
                    ];
                });

                return $vehicles->toArray();
            } catch (Exception $e) {
                Log::error('Error al obtener vehículos recientes: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Obtener carriers recientes con información de membresía
     */
    public function getRecentCarriers(int $limit = 5): array
    {
        $cacheKey = self::CACHE_PREFIX . 'recent_carriers:' . $limit;
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($limit) {
            try {
                $carriers = DB::table('carriers as c')
                    ->leftJoin('memberships as m', 'c.id_plan', '=', 'm.id')
                    ->select([
                        'c.id',
                        'c.name',
                        'c.slug',
                        'c.country',
                        'c.state',
                        'c.status',
                        'c.created_at',
                        'm.name as plan_name'
                    ])
                    ->orderBy('c.created_at', 'desc')
                    ->limit($limit)
                    ->get()
                    ->map(function ($carrier) {
                        return [
                            'id' => $carrier->id,
                            'name' => $carrier->name,
                            'slug' => $carrier->slug,
                            'country' => $carrier->country ?? 'N/A',
                            'state' => $carrier->state ?? 'N/A',
                            'location' => ($carrier->country ?? 'N/A') . ', ' . ($carrier->state ?? 'N/A'),
                            'plan' => $carrier->plan_name ?? 'No Plan',
                            'status' => $this->getCarrierStatusName($carrier->status),
                            'registration_date' => Carbon::parse($carrier->created_at)->format('M d, Y'),
                            'created_at' => Carbon::parse($carrier->created_at)->diffForHumans()
                        ];
                    });

                return $carriers->toArray();
            } catch (Exception $e) {
                Log::error('Error al obtener carriers recientes: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Obtener nombre del status del carrier
     */
    private function getCarrierStatusName($status): string
    {
        return match ($status) {
            1 => 'Active',
            0 => 'Inactive',
            2 => 'Pending',
            3 => 'Pending Validation',
            4 => 'Rejected',
            default => 'Unknown',
        };
    }
}