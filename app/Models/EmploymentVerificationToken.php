<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EmploymentVerificationToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'driver_id',
        'employment_company_id',
        'company_email',
        'company_name',
        'company_contact',
        'expires_at',
        'verified_at',
        'verification_notes',
        'verification_status'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Generate a unique verification token
     *
     * @return string
     */
    public static function generateToken()
    {
        return Str::random(64);
    }

    /**
     * Get the driver associated with this token
     */
    public function driver()
    {
        return $this->belongsTo(\App\Models\UserDriverDetail::class, 'driver_id');
    }

    /**
     * Get the employment company associated with this token
     */
    public function employmentCompany()
    {
        return $this->belongsTo(\App\Models\Admin\Driver\DriverEmploymentCompany::class, 'employment_company_id');
    }
}
