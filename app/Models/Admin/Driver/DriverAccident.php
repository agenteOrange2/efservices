<?php

namespace App\Models\Admin\Driver;

use App\Models\UserDriverDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class DriverAccident extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_driver_detail_id',
        'accident_date',
        'nature_of_accident',
        'had_injuries',
        'number_of_injuries',
        'had_fatalities',
        'number_of_fatalities',
        'comments',
    ];

    protected $casts = [
        'accident_date' => 'date',
        'had_injuries' => 'boolean',
        'had_fatalities' => 'boolean',
    ];

    public function userDriverDetail()
    {
        return $this->belongsTo(UserDriverDetail::class);
    }
}