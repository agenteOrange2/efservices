<?php
namespace App\Models\Admin\Driver;

use App\Models\User;
use App\Models\UserDriverDetail;
use App\Models\OwnerOperatorDetail;
use App\Models\ThirdPartyDetail;
use App\Models\CompanyDriverDetail;
use App\Models\VehicleDriverAssignment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class DriverApplication extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    
    protected $fillable = [
        'user_id',
        'status',        
        'pdf_path',
        'completed_at',
        'rejection_reason'
    ];
    
    protected $casts = [
        'status' => 'string'
    ];
    
    // Constantes para status
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function userDriverDetail()
    {
        return $this->hasOne(UserDriverDetail::class, 'user_id', 'user_id');
    }
    
    public function addresses()
    {
        return $this->hasMany(DriverAddress::class, 'driver_application_id');
    }
    
    public function details()
    {
        return $this->hasOne(DriverApplicationDetail::class, 'driver_application_id');
    }
    
    /**
     * Get Owner Operator details through DriverApplicationDetail and VehicleDriverAssignment
     * Chain: DriverApplication -> DriverApplicationDetail -> VehicleDriverAssignment -> OwnerOperatorDetail
     */
    public function ownerOperatorDetail(): HasOneThrough
    {
        return $this->hasOneThrough(
            OwnerOperatorDetail::class,
            DriverApplicationDetail::class,
            'driver_application_id', // Foreign key on DriverApplicationDetail table
            'vehicle_driver_assignment_id', // Foreign key on OwnerOperatorDetail table
            'id', // Local key on DriverApplication table
            'vehicle_driver_assignment_id' // Local key on DriverApplicationDetail table
        );
    }
    
    /**
     * Get Third Party details through DriverApplicationDetail and VehicleDriverAssignment
     * Chain: DriverApplication -> DriverApplicationDetail -> VehicleDriverAssignment -> ThirdPartyDetail
     */
    public function thirdPartyDetail(): HasOneThrough
    {
        return $this->hasOneThrough(
            ThirdPartyDetail::class,
            DriverApplicationDetail::class,
            'driver_application_id', // Foreign key on DriverApplicationDetail table
            'vehicle_driver_assignment_id', // Foreign key on ThirdPartyDetail table
            'id', // Local key on DriverApplication table
            'vehicle_driver_assignment_id' // Local key on DriverApplicationDetail table
        );
    }

    /**
     * Get Company Driver details through DriverApplicationDetail and VehicleDriverAssignment
     * Chain: DriverApplication -> DriverApplicationDetail -> VehicleDriverAssignment -> CompanyDriverDetail
     */
    public function companyDriverDetail(): HasOneThrough
    {
        return $this->hasOneThrough(
            CompanyDriverDetail::class,
            DriverApplicationDetail::class,
            'driver_application_id', // Foreign key on DriverApplicationDetail table
            'vehicle_driver_assignment_id', // Foreign key on CompanyDriverDetail table
            'id', // Local key on DriverApplication table
            'vehicle_driver_assignment_id' // Local key on DriverApplicationDetail table
        );
    }
    
    /**
     * Determinar si esta aplicación es de tipo Owner Operator.
     */
    public function isOwnerOperator(): bool
    {
        return $this->details && $this->details->applying_position === 'owner_operator';
    }
    
    /**
     * Determinar si esta aplicación es de tipo Third Party Driver.
     */
    public function isThirdPartyDriver(): bool
    {
        return $this->details && $this->details->applying_position === 'third_party_driver';
    }
    
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('application_pdf')
            ->singleFile();
    }
    
    /**
     * Relación con las verificaciones de reclutamiento
     */
    public function verifications()
    {
        return $this->hasMany(DriverRecruitmentVerification::class, 'driver_application_id');
    }
}