<?php

namespace App\Models\Admin\Driver;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DriverApplicationDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_application_id',
        'applying_position',
        'applying_position_other',
        'applying_location',
        'eligible_to_work',
        'can_speak_english',
        'has_twic_card',
        'twic_expiration_date',
        'how_did_hear',
        'how_did_hear_other',
        'referral_employee_name',
        'expected_pay'
    ];

    protected $casts = [
        'eligible_to_work' => 'boolean',
        'can_speak_english' => 'boolean',
        'has_twic_card' => 'boolean',
        'twic_expiration_date' => 'date',
        'expected_pay' => 'decimal:2'
    ];

    public function application()
    {
        return $this->belongsTo(DriverApplication::class, 'driver_application_id');
    }

    // En el modelo ApplicationDetails
    public function getApplyingPositionAttribute($value)
    {
        return $this->applying_position_other ? 'other' : $value;
    }

    public function getHowDidHearAttribute($value)
    {
        return $this->how_did_hear_other ? 'other' : ($this->referral_employee_name ? 'employee_referral' : $value);
    }
}
