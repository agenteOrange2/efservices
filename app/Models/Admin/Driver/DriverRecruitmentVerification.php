<?php

namespace App\Models\Admin\Driver;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DriverRecruitmentVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_application_id',
        'verified_by_user_id',
        'verification_items',
        'notes',
        'verified_at'
    ];

    protected $casts = [
        'verification_items' => 'array',
        'verified_at' => 'datetime'
    ];

    public function application()
    {
        return $this->belongsTo(DriverApplication::class, 'driver_application_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }
}
