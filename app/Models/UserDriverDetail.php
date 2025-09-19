<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\Log;
use App\Models\Admin\Vehicle\Vehicle;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\Driver\DriverAddress;
use App\Models\Admin\Driver\DriverLicense;
use App\Models\Admin\Driver\DriverAccident;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Models\Admin\Driver\DriverFmcsrData;
use App\Models\Admin\Driver\DriverExperience;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Driver\DriverWorkHistory;
use App\Models\Admin\Driver\DriverCertification;
use App\Models\Admin\Driver\DriverCompanyPolicy;
use App\Models\Admin\Driver\DriverTrainingSchool;
use App\Models\Admin\Driver\DriverCriminalHistory;
use App\Models\Admin\Driver\DriverEmploymentCompany;
use App\Models\Admin\Driver\DriverTrafficConviction;
use App\Models\Admin\Driver\DriverUnemploymentPeriod;
use App\Models\Admin\Driver\DriverTesting;
use App\Models\Admin\Driver\DriverInspection;
use App\Models\Admin\Driver\DriverTraining;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\Admin\Driver\DriverMedicalQualification;

class UserDriverDetail extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'carrier_id',
        'middle_name',
        'last_name',
        'phone',
        'date_of_birth',
        'status',
        'terms_accepted',
        'confirmation_token',
        'application_completed',
        'current_step',
        'assigned_vehicle_id',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'notes',
        'hire_date',
        'termination_date',
        'created_by_admin',
        'updated_by_admin',
        'completion_percentage',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'status' => 'integer',
        'terms_accepted' => 'boolean',
        'application_completed' => 'boolean',
        'current_step' => 'integer',
        'assigned_vehicle_id' => 'integer',
        'hire_date' => 'date',
        'termination_date' => 'date',
        'created_by_admin' => 'integer',
        'updated_by_admin' => 'integer',
        'completion_percentage' => 'integer',
    ];

    public function hasRequiredDocuments(): bool
    {
        // Implementar lógica de verificación de documentos
        return true; // Temporalmente para testing
    }

    // Constantes para los valores de status
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_PENDING = 2;
    
    public function getFormattedPhoneAttribute()
    {
        if (!$this->phone) return 'No phone available';

        // Limpiar el número (solo dígitos)
        $phone = preg_replace('/\D/', '', $this->phone);

        // Formatear según la longitud
        if (strlen($phone) === 10) {
            // Formato: (XXX) XXX-XXXX
            return sprintf(
                '(%s) %s-%s',
                substr($phone, 0, 3),
                substr($phone, 3, 3),
                substr($phone, 6, 4)
            );
        } elseif (strlen($phone) === 11 && substr($phone, 0, 1) === '1') {
            // Formato: +1 (XXX) XXX-XXXX
            return sprintf(
                '+1 (%s) %s-%s',
                substr($phone, 1, 3),
                substr($phone, 4, 3),
                substr($phone, 7, 4)
            );
        }

        // Si no coincide con formatos estándar, devolver original
        return $this->phone;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function application()
    {
        return $this->hasOne(DriverApplication::class, 'user_id', 'user_id');
    }
    public function assignedVehicle()
    {
        return $this->belongsTo(Vehicle::class, 'assigned_vehicle_id');
    }

    public function getStatusNameAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_PENDING => 'Pending',
            default => 'Unknown',
        };
    }

    public function getProfilePhotoUrlAttribute()
    {
        $media = $this->getFirstMedia('profile_photo_driver');
        Log::info('Recuperando foto del Driver', [
            'user_driver_id' => $this->id,
            'media_exists' => $media ? true : false,
            'media_url' => $media ? $media->getUrl() : null,
        ]);
        return $media ? $media->getUrl() : asset('build/assets/images/default_profile.png');
    }

    //Media library
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile_photo_driver')
            ->useDisk('public')
            ->singleFile();
            
        $this->addMediaCollection('license_front')
            ->useDisk('public')
            ->singleFile();
            
        $this->addMediaCollection('license_back')
            ->useDisk('public')
            ->singleFile();
    }

    public function registerMediaConversions(Media $media = null): void
    {
        // Deshabilitar conversiones temporalmente para evitar errores
        // Solo aplicar conversiones a fotos de perfil, no a licencias
        if ($media && $media->collection_name === 'profile_photo_driver') {
            $this->addMediaConversion('webp')
                ->format('webp')
                ->keepOriginalImageFormat();
        }
    }

    // Relación con direcciones
    public function addresses()
    {
        return $this->hasMany(DriverAddress::class, 'driver_application_id', 'id');
    }

    //Licencias
    public function licenses()
    {
        return $this->hasMany(DriverLicense::class);
    }

    public function primaryLicense()
    {
        return $this->hasOne(DriverLicense::class)->where('is_primary', true);
    }

    // Experiencia de conducción
    public function experiences()
    {
        return $this->hasMany(DriverExperience::class);
    }

    // Calificación médica
    public function medicalQualification()
    {
        return $this->hasOne(DriverMedicalQualification::class);
    }

    public function workHistories()
    {
        return $this->hasMany(DriverWorkHistory::class, 'user_driver_detail_id');
    }

    public function trainingSchools()
    {
        return $this->hasMany(DriverTrainingSchool::class);
    }

    public function trafficConvictions()
    {
        return $this->hasMany(DriverTrafficConviction::class);
    }

    public function accidents()
    {
        return $this->hasMany(DriverAccident::class);
    }

    public function testings()
    {
        return $this->hasMany(DriverTesting::class);
    }

    public function inspections()
    {
        return $this->hasMany(DriverInspection::class);
    }

    public function fmcsrData()
    {
        return $this->hasOne(DriverFmcsrData::class);
    }

    /**
     * Relación con períodos de desempleo
     */

    public function unemploymentPeriods()
    {
        return $this->hasMany(\App\Models\Admin\Driver\DriverUnemploymentPeriod::class);
    }

    /**
     * Relación con empresas donde ha trabajado
     */
    public function employmentCompanies()
    {
        return $this->hasMany(DriverEmploymentCompany::class);
    }

    /**
     * Relación con empleos relacionados
     */
    public function relatedEmployments()
    {
        return $this->hasMany(\App\Models\Admin\Driver\DriverRelatedEmployment::class);
    }

    /**
     * Relación con empleos relacionados del conductor (nueva tabla)
     */
    public function driver_related_employments()
    {
        return $this->hasMany(\App\Models\Admin\Driver\DriverRelatedEmployment::class, 'user_driver_detail_id');
    }


    public function companyPolicy()
    {
        return $this->hasOne(DriverCompanyPolicy::class);
    }

    public function criminalHistory()
    {
        return $this->hasOne(DriverCriminalHistory::class);
    }

    // En el modelo UserDriverDetail
    public function certification()
    {
        return $this->hasOne(DriverCertification::class, 'user_driver_detail_id');
    }

    // Relación con la tabla de historial de empleos del conductor
    public function driverEmploymentCompanies()
    {
        return $this->hasMany(\App\Models\Admin\Driver\DriverEmploymentCompany::class, 'user_driver_detail_id');
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'user_driver_detail_id');
    }

    /**
     * Relación con los cursos de capacitación del conductor
     */
    public function courses()
    {
        return $this->hasMany(\App\Models\Admin\Driver\DriverCourse::class, 'user_driver_detail_id');
    }

    /**
     * Relación con los entrenamientos asignados al conductor
     */
    public function driverTrainings()
    {
        return $this->hasMany(DriverTraining::class, 'user_driver_detail_id');
    }
}
