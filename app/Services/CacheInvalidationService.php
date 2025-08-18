<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheInvalidationService
{
    /**
     * Invalidar caché relacionado con carriers
     */
    public static function invalidateCarrierCache($carrierId = null)
    {
        $tags = ['carriers', 'dashboard', 'reports', 'statistics'];
        
        Cache::flush();
        
        Log::info('Cache invalidated for carriers', [
            'carrier_id' => $carrierId,
            'tags' => $tags
        ]);
    }

    /**
     * Invalidar caché relacionado con drivers
     */
    public static function invalidateDriverCache($driverId = null)
    {
        $tags = ['drivers', 'dashboard', 'reports', 'statistics'];
        
        Cache::flush();
        
        Log::info('Cache invalidated for drivers', [
            'driver_id' => $driverId,
            'tags' => $tags
        ]);
    }

    /**
     * Invalidar caché relacionado con vehicles
     */
    public static function invalidateVehicleCache($vehicleId = null)
    {
        $tags = ['vehicles', 'dashboard', 'reports', 'statistics'];
        
        Cache::flush();
        
        Log::info('Cache invalidated for vehicles', [
            'vehicle_id' => $vehicleId,
            'tags' => $tags
        ]);
    }

    /**
     * Invalidar caché relacionado con users
     */
    public static function invalidateUserCache($userId = null)
    {
        $tags = ['users', 'dashboard', 'statistics'];
        
        Cache::tags($tags)->flush();
        
        Log::info('Cache invalidated for users', [
            'user_id' => $userId,
            'tags' => $tags
        ]);
    }

    /**
     * Invalidar caché relacionado con maintenance
     */
    public static function invalidateMaintenanceCache($maintenanceId = null)
    {
        $tags = ['maintenance', 'dashboard', 'reports', 'statistics'];
        
        Cache::tags($tags)->flush();
        
        Log::info('Cache invalidated for maintenance', [
            'maintenance_id' => $maintenanceId,
            'tags' => $tags
        ]);
    }

    /**
     * Invalidar todo el caché del dashboard
     */
    public static function invalidateDashboardCache()
    {
        $tags = ['dashboard', 'statistics'];
        
        Cache::flush();
        
        Log::info('Dashboard cache invalidated', [
            'tags' => $tags
        ]);
    }

    /**
     * Invalidar todo el caché de reportes
     */
    public static function invalidateReportsCache()
    {
        $tags = ['reports', 'statistics'];
        
        Cache::flush();
        
        Log::info('Reports cache invalidated', [
            'tags' => $tags
        ]);
    }

    /**
     * Invalidar caché específico por clave
     */
    public static function invalidateSpecificCache(string $key, array $tags = [])
    {
        if (!empty($tags)) {
            Cache::forget($key);
        } else {
            Cache::forget($key);
        }
        
        Log::info('Specific cache invalidated', [
            'key' => $key,
            'tags' => $tags
        ]);
    }

    /**
     * Limpiar caché expirado y optimizar
     */
    public static function cleanupExpiredCache()
    {
        // Esta función se puede usar en comandos programados
        Log::info('Cache cleanup executed');
    }
}