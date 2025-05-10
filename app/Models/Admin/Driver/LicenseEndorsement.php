<?php

namespace App\Models\Admin\Driver;

use Illuminate\Database\Eloquent\Model;

class LicenseEndorsement extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function driverLicenses()
    {
        return $this->belongsToMany(DriverLicense::class, 'driver_license_endorsements')
            ->withPivot('issued_date', 'expiration_date')
            ->withTimestamps();
    }
}
