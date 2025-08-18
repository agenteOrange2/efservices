<?php

namespace App\Services;

use App\Models\Carrier;
use App\Models\UserDriverDetail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class ReportService
{
    private const CACHE_TTL = 3600; // 1 hora
    private const CACHE_PREFIX = 'reports:';

    /**
     * Reporte general del sistema con caché
     */
    public function getSystemOverviewReport(array $filters = []): array
    {
        $cacheKey = self::CACHE_PREFIX . 'system_overview:' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters) {
            try {
                $dateFrom = $filters['date_from'] ?? Carbon::now()->subDays(30);
                $dateTo = $filters['date_to'] ?? Carbon::now();

                return [
                    'carriers' => $this->getCarrierMetrics($dateFrom, $dateTo),
                    'drivers' => $this->getDriverMetrics($dateFrom, $dateTo),
                    'registrations' => $this->getRegistrationMetrics($dateFrom, $dateTo),
                    'activity' => $this->getActivityMetrics($dateFrom, $dateTo),
                    'revenue' => $this->getRevenueMetrics($dateFrom, $dateTo)
                ];
            } catch (Exception $e) {
                Log::error('Error en reporte general del sistema: ' . $e->getMessage());
                throw new Exception('Error al generar el reporte general');
            }
        });
    }

    /**
     * Reporte de carriers con optimización de consultas
     */
    public function getCarrierReport(array $filters = []): array
    {
        try {
            $query = DB::table('carriers as c')
                ->leftJoin('memberships as m', 'c.id_plan', '=', 'm.id')
                ->leftJoin('user_carrier_details as ucd', 'c.id', '=', 'ucd.carrier_id')
                ->select([
                    'c.id',
                    'c.name',
                    'c.status',
                    'c.document_status',
                    'c.created_at',
                    'm.name as membership_name',
                    'm.price as membership_price',
                    DB::raw('COUNT(DISTINCT ucd.user_id) as total_users'),
                    DB::raw('COUNT(DISTINCT CASE WHEN ucd.status = "active" THEN ucd.user_id END) as active_users')
                ])
                ->groupBy('c.id', 'c.name', 'c.status', 'c.document_status', 'c.created_at', 'm.name', 'm.price');

            // Aplicar filtros
            if (!empty($filters['status'])) {
                $query->where('c.status', $filters['status']);
            }

            if (!empty($filters['document_status'])) {
                $query->where('c.document_status', $filters['document_status']);
            }

            if (!empty($filters['membership_id'])) {
                $query->where('c.id_plan', $filters['membership_id']);
            }

            if (!empty($filters['date_from'])) {
                $query->where('c.created_at', '>=', $filters['date_from']);
            }

            if (!empty($filters['date_to'])) {
                $query->where('c.created_at', '<=', $filters['date_to']);
            }

            $carriers = $query->orderBy('c.created_at', 'desc')->get();

            return [
                'data' => $carriers,
                'summary' => [
                    'total_carriers' => $carriers->count(),
                    'active_carriers' => $carriers->where('status', 'active')->count(),
                    'total_revenue' => $carriers->sum('membership_price'),
                    'avg_users_per_carrier' => $carriers->avg('total_users')
                ]
            ];
        } catch (Exception $e) {
            Log::error('Error en reporte de carriers: ' . $e->getMessage());
            throw new Exception('Error al generar el reporte de transportistas');
        }
    }

    /**
     * Reporte de conductores con métricas avanzadas
     */
    public function getDriverReport(array $filters = []): array
    {
        try {
            $query = DB::table('user_driver_details as udd')
                ->join('users as u', 'udd.user_id', '=', 'u.id')
                ->join('carriers as c', 'udd.carrier_id', '=', 'c.id')
                ->select([
                    'udd.id',
                    'u.name as driver_name',
                    'u.email',
                    'u.status as user_status',
                    'udd.license_number',
                    'udd.license_type',
                    'udd.license_expiry',
                    'udd.license_status',
                    'udd.status as driver_status',
                    'udd.hire_date',
                    'c.name as carrier_name',
                    'c.status as carrier_status',
                    DB::raw('DATEDIFF(udd.license_expiry, NOW()) as days_to_expiry'),
                    DB::raw('DATEDIFF(NOW(), udd.hire_date) as days_employed')
                ]);

            // Aplicar filtros
            if (!empty($filters['status'])) {
                $query->where('udd.status', $filters['status']);
            }

            if (!empty($filters['carrier_id'])) {
                $query->where('udd.carrier_id', $filters['carrier_id']);
            }

            if (!empty($filters['license_status'])) {
                $query->where('udd.license_status', $filters['license_status']);
            }

            if (!empty($filters['expiring_soon'])) {
                $query->whereBetween('udd.license_expiry', [now(), now()->addDays(30)]);
            }

            $drivers = $query->orderBy('udd.created_at', 'desc')->get();

            return [
                'data' => $drivers,
                'summary' => [
                    'total_drivers' => $drivers->count(),
                    'active_drivers' => $drivers->where('driver_status', 'active')->count(),
                    'expired_licenses' => $drivers->where('days_to_expiry', '<', 0)->count(),
                    'expiring_soon' => $drivers->whereBetween('days_to_expiry', [0, 30])->count(),
                    'avg_employment_days' => $drivers->avg('days_employed')
                ]
            ];
        } catch (Exception $e) {
            Log::error('Error en reporte de conductores: ' . $e->getMessage());
            throw new Exception('Error al generar el reporte de conductores');
        }
    }

    /**
     * Reporte de registros por período
     */
    public function getRegistrationReport(string $period = 'monthly', array $filters = []): array
    {
        $cacheKey = self::CACHE_PREFIX . "registrations:{$period}:" . md5(serialize($filters));
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($period, $filters) {
            try {
                $dateFormat = $this->getDateFormat($period);
                $dateFrom = $filters['date_from'] ?? Carbon::now()->subMonths(12);
                $dateTo = $filters['date_to'] ?? Carbon::now();

                // Registros de carriers
                $carrierRegistrations = DB::table('carriers')
                    ->select([
                        DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"),
                        DB::raw('COUNT(*) as total'),
                        DB::raw('COUNT(CASE WHEN status = "active" THEN 1 END) as active')
                    ])
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();

                // Registros de conductores
                $driverRegistrations = DB::table('user_driver_details')
                    ->select([
                        DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"),
                        DB::raw('COUNT(*) as total'),
                        DB::raw('COUNT(CASE WHEN status = "active" THEN 1 END) as active')
                    ])
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();

                return [
                    'carriers' => $carrierRegistrations,
                    'drivers' => $driverRegistrations,
                    'summary' => [
                        'total_carrier_registrations' => $carrierRegistrations->sum('total'),
                        'total_driver_registrations' => $driverRegistrations->sum('total'),
                        'avg_carriers_per_period' => $carrierRegistrations->avg('total'),
                        'avg_drivers_per_period' => $driverRegistrations->avg('total')
                    ]
                ];
            } catch (Exception $e) {
                Log::error('Error en reporte de registros: ' . $e->getMessage());
                throw new Exception('Error al generar el reporte de registros');
            }
        });
    }

    /**
     * Reporte de ingresos por membresías
     */
    public function getRevenueReport(array $filters = []): array
    {
        $cacheKey = self::CACHE_PREFIX . 'revenue:' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters) {
            try {
                $query = DB::table('carriers as c')
                    ->join('memberships as m', 'c.id_plan', '=', 'm.id')
                    ->select([
                        'm.id as membership_id',
                        'm.name as membership_name',
                        'm.price',
                        DB::raw('COUNT(c.id) as total_subscribers'),
                        DB::raw('COUNT(CASE WHEN c.status = "active" THEN 1 END) as active_subscribers'),
                        DB::raw('SUM(CASE WHEN c.status = "active" THEN m.price ELSE 0 END) as active_revenue'),
                        DB::raw('SUM(m.price) as total_potential_revenue')
                    ])
                    ->groupBy('m.id', 'm.name', 'm.price');

                if (!empty($filters['date_from'])) {
                    $query->where('c.created_at', '>=', $filters['date_from']);
                }

                if (!empty($filters['date_to'])) {
                    $query->where('c.created_at', '<=', $filters['date_to']);
                }

                $revenue = $query->orderBy('active_revenue', 'desc')->get();

                return [
                    'data' => $revenue,
                    'summary' => [
                        'total_active_revenue' => $revenue->sum('active_revenue'),
                        'total_potential_revenue' => $revenue->sum('total_potential_revenue'),
                        'total_active_subscribers' => $revenue->sum('active_subscribers'),
                        'avg_revenue_per_membership' => $revenue->avg('active_revenue')
                    ]
                ];
            } catch (Exception $e) {
                Log::error('Error en reporte de ingresos: ' . $e->getMessage());
                throw new Exception('Error al generar el reporte de ingresos');
            }
        });
    }

    /**
     * Exportar reporte a CSV
     */
    public function exportToCsv(string $reportType, array $filters = []): string
    {
        try {
            $data = match($reportType) {
                'carriers' => $this->getCarrierReport($filters)['data'],
                'drivers' => $this->getDriverReport($filters)['data'],
                'revenue' => $this->getRevenueReport($filters)['data'],
                default => throw new Exception('Tipo de reporte no válido')
            };

            $filename = storage_path("app/exports/{$reportType}_" . date('Y-m-d_H-i-s') . '.csv');
            
            // Crear directorio si no existe
            if (!file_exists(dirname($filename))) {
                mkdir(dirname($filename), 0755, true);
            }

            $file = fopen($filename, 'w');
            
            // Escribir encabezados
            if ($data->isNotEmpty()) {
                fputcsv($file, array_keys((array) $data->first()));
                
                // Escribir datos
                foreach ($data as $row) {
                    fputcsv($file, (array) $row);
                }
            }
            
            fclose($file);
            
            Log::info("Reporte {$reportType} exportado a CSV: {$filename}");
            
            return $filename;
        } catch (Exception $e) {
            Log::error('Error al exportar reporte: ' . $e->getMessage());
            throw new Exception('Error al exportar el reporte');
        }
    }

    /**
     * Limpiar caché de reportes
     */
    public function clearReportsCache(): bool
    {
        try {
            $keys = [
                self::CACHE_PREFIX . 'system_overview:*',
                self::CACHE_PREFIX . 'registrations:*',
                self::CACHE_PREFIX . 'revenue:*'
            ];

            foreach ($keys as $pattern) {
                Cache::forget($pattern);
            }

            Log::info('Caché de reportes limpiado exitosamente');
            return true;
        } catch (Exception $e) {
            Log::error('Error al limpiar caché de reportes: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Métricas de carriers
     */
    private function getCarrierMetrics($dateFrom, $dateTo): array
    {
        return [
            'total' => Carrier::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'active' => Carrier::where('status', 'active')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'pending_documents' => Carrier::where('document_status', 'pending')->count(),
            'growth_rate' => $this->calculateGrowthRate('carriers', $dateFrom, $dateTo)
        ];
    }

    /**
     * Métricas de conductores
     */
    private function getDriverMetrics($dateFrom, $dateTo): array
    {
        return [
            'total' => UserDriverDetail::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'active' => UserDriverDetail::where('status', 'active')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'licenses_expiring' => UserDriverDetail::whereBetween('license_expiry', [now(), now()->addDays(30)])->count(),
            'growth_rate' => $this->calculateGrowthRate('user_driver_details', $dateFrom, $dateTo)
        ];
    }

    /**
     * Métricas de registros
     */
    private function getRegistrationMetrics($dateFrom, $dateTo): array
    {
        return [
            'carriers_registered' => Carrier::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'drivers_registered' => UserDriverDetail::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'daily_avg_carriers' => Carrier::whereBetween('created_at', [$dateFrom, $dateTo])->count() / max(1, $dateFrom->diffInDays($dateTo)),
            'daily_avg_drivers' => UserDriverDetail::whereBetween('created_at', [$dateFrom, $dateTo])->count() / max(1, $dateFrom->diffInDays($dateTo))
        ];
    }

    /**
     * Métricas de actividad
     */
    private function getActivityMetrics($dateFrom, $dateTo): array
    {
        return [
            'active_carriers' => Carrier::where('status', 'active')->count(),
            'active_drivers' => UserDriverDetail::where('status', 'active')->count(),
            'pending_approvals' => Carrier::where('document_status', 'pending')->count(),
            'system_utilization' => $this->calculateSystemUtilization()
        ];
    }

    /**
     * Métricas de ingresos
     */
    private function getRevenueMetrics($dateFrom, $dateTo): array
    {
        $activeRevenue = DB::table('carriers')
            ->join('memberships', 'carriers.id_plan', '=', 'memberships.id')
            ->where('carriers.status', 'active')
            ->sum('memberships.price');

        return [
            'active_revenue' => $activeRevenue,
            'potential_revenue' => DB::table('carriers')
                ->join('memberships', 'carriers.id_plan', '=', 'memberships.id')
                ->sum('memberships.price'),
            'avg_revenue_per_carrier' => $activeRevenue / max(1, Carrier::where('status', 'active')->count()),
            'revenue_growth' => $this->calculateRevenueGrowth($dateFrom, $dateTo)
        ];
    }

    /**
     * Calcular tasa de crecimiento
     */
    private function calculateGrowthRate(string $table, $dateFrom, $dateTo): float
    {
        $currentPeriod = DB::table($table)->whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $previousPeriod = DB::table($table)
            ->whereBetween('created_at', [
                $dateFrom->copy()->sub($dateTo->diff($dateFrom)),
                $dateFrom
            ])->count();

        return $previousPeriod > 0 ? (($currentPeriod - $previousPeriod) / $previousPeriod) * 100 : 0;
    }

    /**
     * Calcular utilización del sistema
     */
    private function calculateSystemUtilization(): float
    {
        $totalCarriers = Carrier::count();
        $activeCarriers = Carrier::where('status', 'active')->count();
        
        return $totalCarriers > 0 ? ($activeCarriers / $totalCarriers) * 100 : 0;
    }

    /**
     * Calcular crecimiento de ingresos
     */
    private function calculateRevenueGrowth($dateFrom, $dateTo): float
    {
        // Implementar lógica de crecimiento de ingresos
        return 0; // Placeholder
    }

    /**
     * Obtener formato de fecha según período
     */
    private function getDateFormat(string $period): string
    {
        return match($period) {
            'daily' => '%Y-%m-%d',
            'weekly' => '%Y-%u',
            'monthly' => '%Y-%m',
            'yearly' => '%Y',
            default => '%Y-%m'
        };
    }
}