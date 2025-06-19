<?php

namespace App\Models\Admin\Driver;

use App\Models\UserDriverDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Admin\Driver\EmploymentVerificationToken;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class DriverEmploymentCompany extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_driver_detail_id',
        'master_company_id',
        'employed_from',
        'employed_to',
        'positions_held',
        'subject_to_fmcsr',
        'safety_sensitive_function',
        'reason_for_leaving',
        'other_reason_description',
        'email',
        'explanation',
        'email_sent',
        'verification_status',
        'verification_date',
        'verification_notes'
    ];

    protected $casts = [
        'employed_from' => 'date',
        'employed_to' => 'date',
        'subject_to_fmcsr' => 'boolean',
        'safety_sensitive_function' => 'boolean',
        'email_sent' => 'boolean',
        'verification_date' => 'datetime',
    ];


    public function userDriverDetail()
    {
        return $this->belongsTo(UserDriverDetail::class);
    }

    public function company()
    {
        return $this->belongsTo(MasterCompany::class, 'master_company_id');
    }

    // Add the missing relationship
    public function masterCompany()
    {
        return $this->belongsTo(MasterCompany::class);
    }
    
    /**
     * Relación con los tokens de verificación de empleo
     */
    public function verificationTokens()
    {
        return $this->hasMany(EmploymentVerificationToken::class, 'employment_company_id');
    }
    
    /**
     * Registra los tipos de media que puede tener este modelo
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('signature')
            ->singleFile();
    }
}
