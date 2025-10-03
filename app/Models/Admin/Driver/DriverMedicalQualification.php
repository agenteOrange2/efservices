<?php

namespace App\Models\Admin\Driver;

use Spatie\MediaLibrary\HasMedia;
use App\Models\UserDriverDetail;
use App\Models\Carrier;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DriverMedicalQualification extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_driver_detail_id',
        'social_security_number',
        'hire_date',
        'location',
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

    public function userDriverDetail()
    {
        return $this->belongsTo(UserDriverDetail::class, 'user_driver_detail_id');
    }

    // Carrier relationship removed as carrier_id column doesn't exist in the table

    public function driver()
    {
        return $this->hasOneThrough(
            \App\Models\User::class,
            UserDriverDetail::class,
            'id',
            'id',
            'user_driver_detail_id',
            'user_id'
        );
    }

    // Scope for filtering by expiration status
    public function scopeExpiring($query, $days = 30)
    {
        return $query->where('medical_card_expiration_date', '<=', now()->addDays($days))
                    ->where('medical_card_expiration_date', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('medical_card_expiration_date', '<', now());
    }

    // Accessor for status
    public function getStatusAttribute()
    {
        if ($this->medical_card_expiration_date && $this->medical_card_expiration_date < now()) {
            return 'expired';
        } elseif ($this->medical_card_expiration_date && $this->medical_card_expiration_date <= now()->addDays(30)) {
            return 'expiring';
        }
        return 'active';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('medical_certificate')
            ->useDisk('public');
            
        $this->addMediaCollection('test_results')
            ->useDisk('public');
            
        $this->addMediaCollection('additional_documents')
            ->useDisk('public');
            
        // Collection for additional documents uploaded via form
        $this->addMediaCollection('medical_documents')
            ->useDisk('public');
            
        // Keep existing collection for backward compatibility
        $this->addMediaCollection('medical_card')
            ->useDisk('public')
            ->singleFile();
    }
}