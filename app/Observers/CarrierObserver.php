<?php

namespace App\Observers;

use App\Models\Carrier;
use App\Services\CacheInvalidationService;

class CarrierObserver
{
    /**
     * Handle the Carrier "created" event.
     */
    public function created(Carrier $carrier): void
    {
        CacheInvalidationService::invalidateCarrierCache($carrier->id);
    }

    /**
     * Handle the Carrier "updated" event.
     */
    public function updated(Carrier $carrier): void
    {
        CacheInvalidationService::invalidateCarrierCache($carrier->id);
    }

    /**
     * Handle the Carrier "deleted" event.
     */
    public function deleted(Carrier $carrier): void
    {
        CacheInvalidationService::invalidateCarrierCache($carrier->id);
    }

    /**
     * Handle the Carrier "restored" event.
     */
    public function restored(Carrier $carrier): void
    {
        CacheInvalidationService::invalidateCarrierCache($carrier->id);
    }

    /**
     * Handle the Carrier "force deleted" event.
     */
    public function forceDeleted(Carrier $carrier): void
    {
        CacheInvalidationService::invalidateCarrierCache($carrier->id);
    }
}