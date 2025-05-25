<?php

namespace App\Models\Admin\Driver;

use App\Models\UserDriverDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class DriverCourse extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_driver_detail_id',
        'organization_name',
        'phone',
        'city',
        'state',
        'certification_date',
        'experience',
        'expiration_date',
    ];

    protected $casts = [
        'certification_date' => 'date',
        'expiration_date' => 'date',
    ];

    /**
     * Relación con los detalles del conductor
     */
    public function driverDetail()
    {
        return $this->belongsTo(UserDriverDetail::class, 'user_driver_detail_id');
    }

    /**
     * Registra colecciones de medios
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('certificates')
            ->useDisk('public');
    }
}
