<?php

namespace App\Models\Admin\Driver;

use App\Models\UserDriverDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DriverFmcsrData extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_driver_detail_id',
        'is_disqualified',
        'disqualified_details',
        'is_license_suspended',
        'suspension_details',
        'is_license_denied',
        'denial_details',
        'has_positive_drug_test',
        'substance_abuse_professional',
        'sap_phone',
        'return_duty_agency',
        'consent_to_release',
        'has_duty_offenses',
        'recent_conviction_date',
        'offense_details',
        'consent_driving_record'
    ];

    protected $casts = [
        'is_disqualified' => 'boolean',
        'is_license_suspended' => 'boolean',
        'is_license_denied' => 'boolean',
        'has_positive_drug_test' => 'boolean',
        'consent_to_release' => 'boolean',
        'has_duty_offenses' => 'boolean',
        'recent_conviction_date' => 'date',
        'consent_driving_record' => 'boolean',
    ];

    public function userDriverDetail()
    {
        return $this->belongsTo(UserDriverDetail::class);
    }
}