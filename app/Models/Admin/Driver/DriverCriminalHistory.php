<?php

namespace App\Models\Admin\Driver;

use App\Models\UserDriverDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DriverCriminalHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_driver_detail_id',
        'has_criminal_charges',
        'has_felony_conviction',
        'has_minister_permit',
        'fcra_consent',
        'background_info_consent'
    ];

    protected $casts = [
        'has_criminal_charges' => 'boolean',
        'has_felony_conviction' => 'boolean',
        'has_minister_permit' => 'boolean',
        'fcra_consent' => 'boolean',
        'background_info_consent' => 'boolean'
    ];

    public function userDriverDetail()
    {
        return $this->belongsTo(UserDriverDetail::class);
    }
}