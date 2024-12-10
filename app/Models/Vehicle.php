<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'plate_number',
        'model',
        'manufacturer',
        'year',
        'carrier_id',
    ];

    // Relación con el carrier
    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    // Conductores asignados
    public function drivers()
    {
        return $this->hasMany(Driver::class, 'assigned_vehicle_id');
    }
}
