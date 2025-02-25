<?php

namespace App\Models\Admin\Vehicle;

use App\Models\Carrier;
use App\Models\UserDriverDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'carrier_id',
        'make',
        'model',
        'type',
        'company_unit_number',
        'year',
        'vin',
        'gvwr',
        'registration_state',
        'registration_number',
        'registration_expiration_date',
        'permanent_tag',
        'tire_size',
        'fuel_type',
        'irp_apportioned_plate',
        'ownership_type',
        'location',
        'user_driver_detail_id',
        'annual_inspection_expiration_date',
        'out_of_service',
        'out_of_service_date',
        'suspended',
        'suspended_date',
        'notes',
    ];

    protected $casts = [
        'permanent_tag' => 'boolean',
        'irp_apportioned_plate' => 'boolean',
        'out_of_service' => 'boolean',
        'suspended' => 'boolean',
        'registration_expiration_date' => 'date',
        'annual_inspection_expiration_date' => 'date',
        'out_of_service_date' => 'date',
        'suspended_date' => 'date',
    ];

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(UserDriverDetail::class, 'user_driver_detail_id');
    }

    public function serviceItems(): HasMany
    {
        return $this->hasMany(VehicleServiceItem::class);
    }

    public function vehicleMake(): BelongsTo
    {
        return $this->belongsTo(VehicleMake::class, 'make', 'name');
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class, 'type', 'name');
    }
}