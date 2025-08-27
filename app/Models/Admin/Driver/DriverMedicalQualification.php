<?php

namespace App\Models\Admin\Driver;

use Spatie\MediaLibrary\HasMedia;
use App\Models\UserDriverDetail;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DriverMedicalQualification extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_driver_detail_id',
        'social_security_number', // Asegúrate de que este campo esté aquí
        'hire_date',              // Asegúrate de que este campo esté aquí
        'location',               // Asegúrate de que este campo esté aquí
        'is_suspended',
        'suspension_date',
        'is_terminated',
        'termination_date',
        'medical_examiner_name',
        'medical_examiner_registry_number',
        'medical_card_expiration_date'
    ];

    protected $casts = [
        'hire_date' => 'date',
        'is_suspended' => 'boolean',
        'suspension_date' => 'date',
        'is_terminated' => 'boolean',
        'termination_date' => 'date',
        'medical_card_expiration_date' => 'date'
    ];

    public function driverDetail()
    {
        return $this->belongsTo(UserDriverDetail::class, 'user_driver_detail_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('medical_card')
            ->useDisk('public')
            ->singleFile();
    }
}