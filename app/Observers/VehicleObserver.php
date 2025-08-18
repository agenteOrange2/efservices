<?php

namespace App\Observers;

use App\Models\Vehicle;
use App\Services\CacheInvalidationService;

class VehicleObserver
{
    /**
     * Handle the Vehicle "created" event.
     */
    public function created(Vehicle $vehicle): void
    {
        CacheInvalidationService::invalidateVehicleCache($vehicle->id);
    }

    /**
     * Handle the Vehicle "updated" event.
     */
    public function updated(Vehicle $vehicle): void
    {
        CacheInvalidationService::invalidateVehicleCache($vehicle->id);
    }

    /**
     * Handle the Vehicle "deleted" event.
     */
    public function deleted(Vehicle $vehicle): void
    {
        CacheInvalidationService::invalidateVehicleCache($vehicle->id);
    }

    /**
     * Handle the Vehicle "restored" event.
     */
    public function restored(Vehicle $vehicle): void
    {
        CacheInvalidationService::invalidateVehicleCache($vehicle->id);
    }

    /**
     * Handle the Vehicle "force deleted" event.
     */
    public function forceDeleted(Vehicle $vehicle): void
    {
        CacheInvalidationService::invalidateVehicleCache($vehicle->id);
    }
}