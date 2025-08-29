<?php

namespace App\Observers;

use App\Models\Driver;
use App\Services\CacheInvalidationService;

class DriverObserver
{
    /**
     * Handle the Driver "created" event.
     */
    public function created(Driver $driver): void
    {
        CacheInvalidationService::invalidateDriverCache($driver->id);
    }

    /**
     * Handle the Driver "updated" event.
     */
    public function updated(Driver $driver): void
    {
        CacheInvalidationService::invalidateDriverCache($driver->id);
    }

    /**
     * Handle the Driver "deleted" event.
     */
    public function deleted(Driver $driver): void
    {
        CacheInvalidationService::invalidateDriverCache($driver->id);
    }

    /**
     * Handle the Driver "restored" event.
     */
    public function restored(Driver $driver): void
    {
        CacheInvalidationService::invalidateDriverCache($driver->id);
    }

    /**
     * Handle the Driver "force deleted" event.
     */
    public function forceDeleted(Driver $driver): void
    {
        CacheInvalidationService::invalidateDriverCache($driver->id);
    }
}