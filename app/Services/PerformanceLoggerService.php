<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PerformanceLoggerService
{
    /**
     * Log cache performance metrics
     */
    public static function logCachePerformance(string $operation, string $key, float $executionTime, bool $cacheHit = false, array $context = [])
    {
        Log::channel('performance')->info('Cache Performance', [
            'operation' => $operation,
            'cache_key' => $key,
            'execution_time_ms' => round($executionTime * 1000, 2),
            'cache_hit' => $cacheHit,
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'context' => $context,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Log database query performance
     */
    public static function logDatabasePerformance(string $operation, int $queryCount, float $executionTime, array $context = [])
    {
        Log::channel('performance')->info('Database Performance', [
            'operation' => $operation,
            'query_count' => $queryCount,
            'execution_time_ms' => round($executionTime * 1000, 2),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'context' => $context,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Log controller performance
     */
    public static function logControllerPerformance(string $controller, string $method, float $executionTime, array $metrics = [])
    {
        Log::channel('performance')->info('Controller Performance', [
            'controller' => $controller,
            'method' => $method,
            'execution_time_ms' => round($executionTime * 1000, 2),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'metrics' => $metrics,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Log Redis performance metrics
     */
    public static function logRedisPerformance(string $operation, string $key, float $executionTime, $result = null)
    {
        Log::channel('performance')->info('Redis Performance', [
            'operation' => $operation,
            'key' => $key,
            'execution_time_ms' => round($executionTime * 1000, 2),
            'result_size' => $result ? strlen(serialize($result)) : 0,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Log system performance metrics
     */
    public static function logSystemMetrics(array $customMetrics = [])
    {
        $metrics = array_merge([
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'cpu_usage' => sys_getloadavg()[0] ?? 0,
            'active_connections' => DB::select('SHOW STATUS LIKE "Threads_connected"')[0]->Value ?? 0,
            'cache_hits' => self::getCacheHitRate(),
            'timestamp' => now()->toISOString()
        ], $customMetrics);

        Log::channel('performance')->info('System Metrics', $metrics);
    }

    /**
     * Get cache hit rate (approximation)
     */
    private static function getCacheHitRate(): float
    {
        try {
            // Esta es una aproximación - en producción se podría usar Redis INFO
            return 0.85; // Placeholder - implementar lógica real según el driver de caché
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    /**
     * Log performance comparison (before/after optimization)
     */
    public static function logPerformanceComparison(string $operation, float $beforeTime, float $afterTime, array $context = [])
    {
        $improvement = (($beforeTime - $afterTime) / $beforeTime) * 100;
        
        Log::channel('performance')->info('Performance Comparison', [
            'operation' => $operation,
            'before_time_ms' => round($beforeTime * 1000, 2),
            'after_time_ms' => round($afterTime * 1000, 2),
            'improvement_percent' => round($improvement, 2),
            'context' => $context,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Log slow operations (threshold-based)
     */
    public static function logSlowOperation(string $operation, float $executionTime, float $threshold = 1.0, array $context = [])
    {
        if ($executionTime > $threshold) {
            Log::channel('performance')->warning('Slow Operation Detected', [
                'operation' => $operation,
                'execution_time_ms' => round($executionTime * 1000, 2),
                'threshold_ms' => round($threshold * 1000, 2),
                'context' => $context,
                'timestamp' => now()->toISOString()
            ]);
        }
    }
}