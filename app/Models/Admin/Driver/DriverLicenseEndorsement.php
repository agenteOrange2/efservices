<?php

namespace App\Models\Admin\Driver;

use Illuminate\Database\Eloquent\Model;

class DriverLicenseEndorsement extends Model
{
    protected $table = 'driver_license_endorsements';

    protected $fillable = [
        'driver_license_id',
        'license_endorsement_id',
        'issued_date',
        'expiration_date'
    ];

    protected $casts = [
        'issued_date' => 'date',
        'expiration_date' => 'date'
    ];

    public function driverLicense()
    {
        return $this->belongsTo(DriverLicense::class);
    }

    public function endorsement()
    {
        return $this->belongsTo(LicenseEndorsement::class, 'license_endorsement_id');
    }
}
