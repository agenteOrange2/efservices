<?php

namespace App\Models\Admin\Vehicle;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleVerification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vehicle_id',
        'token',
        'driver_application_id',
        'third_party_name',
        'third_party_email',
        'third_party_phone',
        'verified',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'verified' => 'boolean',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the vehicle that owns the verification.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the driver application that owns the verification.
     */
    public function driverApplication()
    {
        return $this->belongsTo(\App\Models\Admin\Driver\DriverApplication::class);
    }
}
