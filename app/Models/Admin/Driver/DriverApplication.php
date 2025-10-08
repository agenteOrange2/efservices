<?php
namespace App\Models\Admin\Driver;

use App\Models\User;
use App\Models\UserDriverDetail;
use App\Models\OwnerOperatorDetail;
use App\Models\ThirdPartyDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        return $this->hasMany(DriverAddress::class);
    }
    
    public function details()
    {
        return $this->hasOne(DriverApplicationDetail::class, 'driver_application_id');
    }
    
    /**
     * Obtener los detalles de Owner Operator asociados a esta aplicación.
     */
    public function ownerOperatorDetail(): HasOne
    {
        return $this->hasOne(OwnerOperatorDetail::class, 'driver_application_id');
    }
    
    /**
     * Obtener los detalles de Third Party asociados a esta aplicación.
     * Note: ThirdPartyDetail now uses assignment_id instead of driver_application_id
     */
    public function thirdPartyDetail(): HasOne
    {
        // This relationship is deprecated as ThirdPartyDetail now uses assignment_id
        // Use the assignment relationship instead
        return $this->hasOne(ThirdPartyDetail::class, 'driver_application_id');
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