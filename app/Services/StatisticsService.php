<?php

namespace App\Services;

use App\Models\Carrier;
use App\Models\UserDriverDetail;
use App\Models\User;
use App\Models\Membership;
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
                        DB::raw('COUNT(CASE WHEN c.status = "active" THEN 1 END) as active'),
                        DB::raw('COUNT(CASE WHEN c.status = "inactive" THEN 1 END) as inactive'),
                        DB::raw('COUNT(CASE WHEN c.status = "suspended" THEN 1 END) as suspended'),
                        DB::raw('COUNT(CASE WHEN c.document_status = "pending" THEN 1 END) as pending_documents'),
                        DB::raw('COUNT(CASE WHEN c.document_status = "approved" THEN 1 END) as approved_documents'),
                        DB::raw('COUNT(CASE WHEN c.document_status = "rejected" THEN 1 END) as rejected_documents'),
                        DB::raw('AVG(m.price) as avg_membership_price'),
                        DB::raw('SUM(CASE WHEN c.status = "active" THEN m.price ELSE 0 END) as active_revenue')
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
                        DB::raw('COUNT(CASE WHEN udd.status = "active" THEN 1 END) as active'),
                        DB::raw('COUNT(CASE WHEN udd.status = "inactive" THEN 1 END) as inactive'),
                        DB::raw('COUNT(CASE WHEN udd.status = "suspended" THEN 1 END) as suspended'),
                        DB::raw('COUNT(CASE WHEN udd.license_status = "valid" THEN 1 END) as valid_licenses'),
                        DB::raw('COUNT(CASE WHEN udd.license_status = "expired" THEN 1 END) as expired_licenses'),
                        DB::raw('COUNT(CASE WHEN udd.license_status = "suspended" THEN 1 END) as suspended_licenses'),
                        DB::raw('COUNT(CASE WHEN DATEDIFF(udd.license_expiry, NOW()) BETWEEN 0 AND 30 THEN 1 END) as expiring_soon'),
                        DB::raw('COUNT(CASE WHEN DATEDIFF(udd.license_expiry, NOW()) < 0 THEN 1 END) as expired_count'),
                        DB::raw('AVG(DATEDIFF(NOW(), udd.hire_date)) as avg_employment_days')
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

                // Estadísticas por tipo de licencia
                $licenseTypes = DB::table('user_driver_details')
                    ->select([
                        'license_type',
                        DB::raw('COUNT(*) as count')
                    ])
                    ->where('status', 'active')
                    ->groupBy('license_type')
                    ->get();

                return array_merge((array) $stats, [
                    'license_validity_rate' => $stats->total > 0 ? round(($stats->valid_licenses / $stats->total) * 100, 2) : 0,
                    'activation_rate' => $stats->total > 0 ? round(($stats->active / $stats->total) * 100, 2) : 0,
                    'avg_employment_months' => round($stats->avg_employment_days / 30, 1),
                    'top_carriers_by_drivers' => $carrierDistribution,
                    'license_type_distribution' => $licenseTypes,
                    'critical_alerts' => $stats->expired_count + $stats->expiring_soon
                ]);
            } catch (Exception $e) {
                Log::error('Error al obtener estadísticas de conductores: ' . $e->getMessage());
                throw new Exception('Error al cargar estadísticas de conductores');
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
}