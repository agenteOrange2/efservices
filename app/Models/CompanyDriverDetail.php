<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyDriverDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_driver_assignment_id',
        'carrier_id',
        'notes'
    ];

    protected $casts = [
        // No casts needed for simplified structure
    ];

    /**
     * Get the vehicle driver assignment that owns this company driver detail
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(VehicleDriverAssignment::class, 'vehicle_driver_assignment_id');
    }

    /**
     * Get the carrier that owns this company driver detail
     */
    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class, 'carrier_id');
    }

    /**
     * Get the driver application through the assignment
     */
    public function driverApplication()
    {
        return $this->assignment->driverApplication();
    }



    /**
     * Get the vehicle through the assignment
     */
    public function vehicle()
    {
        return $this->assignment->vehicle();
    }

    /**
     * Get the user (driver) through the assignment
     */
    public function user()
    {
        return $this->assignment->user();
    }

    /**
     * Scope to filter by carrier
     */
    public function scopeByCarrier($query, $carrierId)
    {
        return $query->where('carrier_id', $carrierId);
    }
}