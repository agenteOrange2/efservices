<?php

namespace App\Models\Admin\Driver;

use App\Models\UserDriverDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DriverExperience extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_driver_detail_id',
        'equipment_type',
        'years_experience',
        'miles_driven',
        'requires_cdl'
    ];

    protected $casts = [
        'years_experience' => 'integer',
        'miles_driven' => 'integer',
        'requires_cdl' => 'boolean'
    ];

    public function driverDetail()
    {
        return $this->belongsTo(UserDriverDetail::class, 'user_driver_detail_id');
    }
}