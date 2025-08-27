<?php

namespace App\Models\Admin\Driver;

use Spatie\MediaLibrary\HasMedia;
use App\Models\UserDriverDetail;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;

class DriverLicense extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'user_driver_detail_id',
        'current_license_number',
        'license_number',
        'state_of_issue',
        'license_class',
        'expiration_date',
        'is_cdl',
        'restrictions',
        'status',
        'is_primary'
    ];

    protected $casts = [
        'expiration_date' => 'date',
        'is_cdl' => 'boolean',
        'is_primary' => 'boolean'
    ];

    public function driverDetail()
    {
        return $this->belongsTo(UserDriverDetail::class, 'user_driver_detail_id');
    }

    public function endorsements()
    {
        return $this->belongsToMany(LicenseEndorsement::class, 'driver_license_endorsements')
            ->withPivot('issued_date', 'expiration_date')
            ->withTimestamps();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('license_front')
            ->useDisk('public')
            ->singleFile();
            
        $this->addMediaCollection('license_back')
            ->useDisk('public')
            ->singleFile();
    }
}
