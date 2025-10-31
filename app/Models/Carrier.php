<?php

namespace App\Models;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;


class Carrier extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;


    protected $fillable = [
        'name',
        'slug',
        'referrer_token',
        'address',
        'state',
        'zipcode',
        'country',
        'ein_number',
        'dot_number',
        'mc_number',
        'state_dot',
        'ifta_account',
        'ifta',
        'business_type',
        'years_in_business',
        'fleet_size',
        'user_id',
        'id_plan',
        'membership_id',
        'documents_ready',
        'terms_accepted_at',
        'status',
        'document_status',
        'documents_completed',
        'documents_completed_at',
        'referrer_token_expires_at',
    ];

    // Agregar constantes para limitar valores de status
    protected $casts = [
        'status' => 'integer',
        'documents_completed' => 'boolean',
        'documents_completed_at' => 'datetime',
        'referrer_token_expires_at' => 'datetime'
    ];

    // Agregar scopes útiles
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Relación con los usuarios (UserCarrier).
     */
    public function users()
    {
        return $this->hasManyThrough(
            User::class,
            UserCarrierDetail::class, // Tabla intermedia
            'carrier_id', // Foreign key en UserCarrierDetail
            'id',         // Foreign key en User
            'id',         // Local key en Carrier
            'user_id'     // Local key en UserCarrierDetail
        );
    }

    // Boot para generar el referrer_token y slug automáticamente
    protected static function booted()
    {
        static::creating(function ($carrier) {
            $carrier->referrer_token = $carrier->referrer_token ?? Str::random(16);
            $carrier->slug = $carrier->slug ?? Str::slug($carrier->name);
        });
    }

    // Constantes para los valores de status
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_PENDING = 2;
    public const STATUS_PENDING_VALIDATION = 3;
    public const STATUS_REJECTED = 4;

    public const DOCUMENT_STATUS_PENDING = 'pending';
    public const DOCUMENT_STATUS_IN_PROGRESS = 'in_progress';
    public const DOCUMENT_STATUS_COMPLETED = 'completed';
    public const DOCUMENT_STATUS_SKIPPED = 'skipped';

    // Relación con usuarios (manager)
    public function managers()
    {
        return $this->belongsToMany(User::class, 'user_carrier')
            ->withPivot('status')
            ->withTimestamps();
    }

    // Relación con documentos
    public function documents()
    {
        return $this->hasMany(CarrierDocument::class);
    }

    // Modelo Carrier
    public function userCarriers()
    {
        return $this->hasMany(UserCarrierDetail::class, 'carrier_id', 'id');
    }

    
    //Asociamos modelo Drivers
    public function userDrivers()
    {
        return $this->hasMany(UserDriverDetail::class, 'carrier_id', 'id');
    }


    //Asociamos modelo Membershio
    public function membership()
    {
        return $this->belongsTo(Membership::class, 'id_plan');
    }

    // Método de acceso para obtener el nombre del status
    public function getStatusNameAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PENDING_VALIDATION => 'Pending Validation',
            self::STATUS_REJECTED => 'Rejected',
            default => 'Unknown',
        };
    }

    public function generateReferrerToken(): void
    {
        $this->referrer_token = Str::random(16);
        $this->save();
    }

    //Media library
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo_carrier')
            ->useDisk('public') // Asegúrate de usar el disco público
            ->singleFile(); // Solo permite un archivo por colección
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('webp')
            ->format('webp')
            ->keepOriginalImageFormat();
    }
    public function getMediaDirectoryAttribute(): string
    {
        return "carrier/{$this->id}/";
    }

    public function getMediaFileNameAttribute(): string
    {
        return "{$this->name}.webp";
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    // Relación con usuarios asignados
    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'user_carrier_access');
    }

    // Agregar métodos para verificación de límites Drivers
    public function canAddDriver(): bool
    {
        return $this->userDrivers()->count() < $this->membership->max_drivers;
    }

    public function canAddVehicle(): bool
    {
        return $this->vehicles()->count() < $this->membership->max_vehicles;
    }
    
    // Relación con vehículos
    public function vehicles()
    {
        return $this->hasMany(\App\Models\Admin\Vehicle\Vehicle::class);
    }

    /**
     * Verificar si todos los documentos requeridos están aprobados
     */
    public function checkDocumentsCompletion()
    {
        $totalDocumentTypes = \App\Models\DocumentType::count();
        $approvedDocuments = $this->documents()
            ->where('status', \App\Models\CarrierDocument::STATUS_APPROVED)
            ->count();
        
        $isCompleted = ($approvedDocuments === $totalDocumentTypes && $totalDocumentTypes > 0);
        
        // Solo actualizar si hay un cambio
        if ($this->documents_completed !== $isCompleted) {
            $this->update([
                'documents_completed' => $isCompleted,
                'documents_completed_at' => $isCompleted ? now() : null
            ]);
            
            \Log::info('Documents completion status updated', [
                'carrier_id' => $this->id,
                'documents_completed' => $isCompleted,
                'approved_documents' => $approvedDocuments,
                'total_document_types' => $totalDocumentTypes
            ]);
        }
        
        return $isCompleted;
    }

    // Relación con detalles bancarios
    public function bankingDetails()
    {
        return $this->hasOne(CarrierBankingDetail::class);
    }
}
