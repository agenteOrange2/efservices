<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Membership extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',         // Nombre del plan
        'price',        // Precio del Plan
        'max_drivers',  //Máximo de conductores permitidos
        'max_vehicles', //Máximo de vehículos permitidos
    ];

    //Relación con los transportistas
    public function carriers()
    {  
        return $this->hasMany(Carrier::class, 'id_plan');
    }
}
