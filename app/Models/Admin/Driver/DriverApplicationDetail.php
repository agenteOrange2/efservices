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
        'applying_location',
        'eligible_to_work',
        'can_speak_english',
        'has_twic_card',
        'twic_expiration_date',
        'how_did_hear',
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
}