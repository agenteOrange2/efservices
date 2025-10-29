<?php

namespace App\Models\Admin\Driver;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\OwnerOperatorDetail;
use App\Models\ThirdPartyDetail;
use App\Models\Admin\Vehicle\VehicleDriverAssignment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DriverApplicationDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_application_id',
        'vehicle_driver_assignment_id',
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
        'expected_pay',
        'vehicle_id',
        // Owner Operator fields
        'owner_name',
        'owner_phone',
        'owner_email',

        // Third Party Company Driver fields
        'third_party_name',
        'third_party_phone',
        'third_party_email',
        'third_party_dba',
        'third_party_address',
        'third_party_contact',
        'third_party_fein',
        'email_sent',
    ];

    protected $casts = [
        'eligible_to_work' => 'boolean',
        'can_speak_english' => 'boolean',
        'has_twic_card' => 'boolean',
        'twic_expiration_date' => 'date',
        'expected_pay' => 'decimal:2',
        'has_completed_employment_history' => 'boolean',
        'vehicle_year' => 'integer',
    ];

    public function application()
    {
        return $this->belongsTo(DriverApplication::class, 'driver_application_id');
    }

    // En el modelo ApplicationDetails
    // REMOVED: Problematic accessor that was interfering with applying_position field
    // public function getApplyingPositionAttribute($value)
    // {
    //     return $this->applying_position_other ? 'other' : $value;
    // }

    public function getHowDidHearAttribute($value)
    {
        return $this->how_did_hear_other ? 'other' : ($this->referral_employee_name ? 'employee_referral' : $value);
    }
    
    /**
     * Relación con el vehículo
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
    
    /**
     * Get Owner Operator details through VehicleDriverAssignment
     * Note: OwnerOperatorDetail now uses vehicle_driver_assignment_id instead of driver_application_id
     */
    public function ownerOperatorDetail()
    {
        return $this->vehicleDriverAssignment ? $this->vehicleDriverAssignment->ownerOperatorDetail : null;
    }
    
    /**
     * Get Third Party details through VehicleDriverAssignment
     * Note: ThirdPartyDetail now uses vehicle_driver_assignment_id instead of driver_application_id
     */
    public function thirdPartyDetail()
    {
        return $this->vehicleDriverAssignment ? $this->vehicleDriverAssignment->thirdPartyDetail : null;
    }
    
    /**
     * Determinar si esta aplicación es de tipo Owner Operator
     */
    public function isOwnerOperator(): bool
    {
        return $this->applying_position === 'owner_operator';
    }
    
    /**
     * Determinar si esta aplicación es de tipo Third Party Driver
     */
    public function isThirdPartyDriver(): bool
    {
        return $this->applying_position === 'third_party_driver';
    }
    
    /**
     * Relación con VehicleDriverAssignment
     */
    public function vehicleDriverAssignment(): BelongsTo
    {
        return $this->belongsTo(VehicleDriverAssignment::class, 'vehicle_driver_assignment_id');
    }
}
