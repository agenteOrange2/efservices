<?php

namespace App\Models\Admin\Driver;

use App\Models\UserDriverDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DriverCertification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_driver_detail_id',
        'signature',
        'is_accepted',
        'signed_at'
    ];

    protected $casts = [
        'is_accepted' => 'boolean',
        'signed_at' => 'datetime'
    ];

    public function userDriverDetail()
    {
        return $this->belongsTo(UserDriverDetail::class);
    }
}