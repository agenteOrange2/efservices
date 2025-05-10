<?php

namespace App\Models\Admin\Vehicle;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleServiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'unit',
        'service_date',
        'next_service_date',
        'service_tasks',
        'vendor_mechanic',
        'description',
        'cost',
        'odometer',
    ];

    protected $casts = [
        'service_date' => 'date',
        'next_service_date' => 'date',
        'cost' => 'decimal:2',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}