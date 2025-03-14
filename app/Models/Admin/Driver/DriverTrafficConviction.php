<?php

namespace App\Models\Admin\Driver;

use App\Models\UserDriverDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DriverTrafficConviction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_driver_detail_id',
        'conviction_date',
        'location',
        'charge',
        'penalty',
    ];

    protected $casts = [
        'conviction_date' => 'date',
    ];

    public function userDriverDetail()
    {
        return $this->belongsTo(UserDriverDetail::class);
    }
}