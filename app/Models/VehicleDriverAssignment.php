<?php

namespace App\Models;

use App\Models\Admin\Vehicle\Vehicle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class VehicleDriverAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'user_driver_detail_id',
        'start_date',
        'end_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the vehicle that owns the assignment.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the driver that owns the assignment.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(UserDriverDetail::class, 'user_driver_detail_id');
    }

    /**
     * Get the owner operator detail for this assignment.
     */
    public function ownerOperatorDetail(): HasOne
    {
        return $this->hasOne(OwnerOperatorDetail::class, 'vehicle_driver_assignment_id');
    }

    /**
     * Get the third party detail for this assignment.
     */
    public function thirdPartyDetail(): HasOne
    {
        return $this->hasOne(ThirdPartyDetail::class, 'vehicle_driver_assignment_id');
    }

    /**
     * Get the company driver detail for this assignment.
     */
    public function companyDriverDetail(): HasOne
    {
        return $this->hasOne(CompanyDriverDetail::class, 'vehicle_driver_assignment_id');
    }

    /**
     * Scope a query to only include active assignments.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include current assignments (active and within date range).
     */
    public function scopeCurrent($query)
    {
        $today = Carbon::today();
        return $query->where('status', 'active')
                    ->where('start_date', '<=', $today)
                    ->where(function($q) use ($today) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', $today);
                    });
    }

    /**
     * Scope a query to only include assignments for a specific vehicle.
     */
    public function scopeForVehicle($query, $vehicleId)
    {
        return $query->where('vehicle_id', $vehicleId);
    }

    /**
     * Scope a query to only include assignments for a specific driver.
     */
    public function scopeForDriver($query, $driverId)
    {
        return $query->where('user_driver_detail_id', $driverId);
    }

    /**
     * Check if the assignment is currently active.
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $today = Carbon::today();
        
        if ($this->start_date > $today) {
            return false;
        }

        if ($this->end_date && $this->end_date < $today) {
            return false;
        }

        return true;
    }

    /**
     * Get the duration of the assignment in days.
     */
    public function getDurationInDays(): ?int
    {
        if (!$this->end_date) {
            return null; // Ongoing assignment
        }

        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * End the assignment by setting end date to today and status to inactive.
     */
    public function end(): bool
    {
        return $this->update([
            'end_date' => Carbon::today(),
            'status' => 'inactive'
        ]);
    }

    /**
     * Check if there are overlapping assignments for the same vehicle and driver.
     */
    public static function hasOverlappingAssignment($vehicleId, $driverId, $startDate, $endDate = null, $excludeId = null): bool
    {
        $query = static::where('vehicle_id', $vehicleId)
                      ->where('user_driver_detail_id', $driverId)
                      ->where('status', 'active');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $query->where(function($q) use ($startDate, $endDate) {
            if ($endDate) {
                // Check for any overlap with a defined end date
                $q->where(function($subQ) use ($startDate, $endDate) {
                    $subQ->where('start_date', '<=', $endDate)
                         ->where(function($dateQ) use ($startDate) {
                             $dateQ->whereNull('end_date')
                                   ->orWhere('end_date', '>=', $startDate);
                         });
                });
            } else {
                // Check for overlap with an ongoing assignment (no end date)
                $q->where(function($subQ) use ($startDate) {
                    $subQ->whereNull('end_date')
                         ->orWhere('end_date', '>=', $startDate);
                })->where('start_date', '<=', $startDate);
            }
        });

        return $query->exists();
    }

    /**
     * Get assignments that are expiring soon (within specified days).
     */
    public static function getExpiringSoon($days = 30)
    {
        $futureDate = Carbon::today()->addDays($days);
        
        return static::where('status', 'active')
                    ->whereNotNull('end_date')
                    ->whereBetween('end_date', [Carbon::today(), $futureDate])
                    ->with(['vehicle', 'driver'])
                    ->orderBy('end_date')
                    ->get();
    }
}