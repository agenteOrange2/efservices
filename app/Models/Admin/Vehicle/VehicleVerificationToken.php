<?php

namespace App\Models\Admin\Vehicle;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleVerificationToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'driver_application_id',
        'vehicle_id',
        'third_party_name',
        'third_party_email',
        'third_party_phone',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driverApplication()
    {
        return $this->belongsTo(\App\Models\Admin\Driver\DriverApplication::class, 'driver_application_id');
    }
}
