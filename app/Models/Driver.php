<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    // use HasFactory;

    // protected $fillable = [
    //     'carrier_id',
    //     'first_name',
    //     'last_name',
    //     'email',
    //     'password',
    //     'license_number',
    //     'birth_date',
    //     'years_experience',
    //     'phone',
    //     'address',
    //     'profile_photo',
    //     'hire_date',
    //     'status',
    //     'assigned_vehicle_id',
    // ];


    // // Relación con el carrier
    // public function carrier()
    // {
    //     return $this->belongsTo(Carrier::class);
    // }

    // // Relación con el vehículo asignado
    // public function assignedVehicle()
    // {
    //     return $this->belongsTo(Vehicle::class, 'assigned_vehicle_id');
    // }
}
