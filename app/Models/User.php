<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\Log;
use Laravel\Jetstream\HasProfilePhoto;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\InteractsWithMedia;
use Laravel\Fortify\TwoFactorAuthenticatable;
use App\Models\Admin\Driver\DriverApplication;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Foundation\Auth\User as Authenticatable;



class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, HasFactory, Notifiable, InteractsWithMedia, HasRoles;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;    
    use TwoFactorAuthenticatable;
    use InteractsWithMedia;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
    ];

    protected $dates = ['created_at', 'updated_at'];

    // Podrías simplificar las relaciones polimórficas
    protected $with = ['carrierDetails', 'driverDetails']; // Eager loading por defecto

    // Agregar método helper
    public function isCarrierUser(): bool
    {
        return $this->hasRole('user_carrier') && $this->carrierDetails()->exists();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relación con los detalles específicos de UserCarrier.
     */
    public function carrierDetails()
    {
        return $this->hasOne(UserCarrierDetail::class, 'user_id', 'id');
    }

    /**
     * Relación con los detalles específicos de UserDriver.
     */
    public function driverDetails()
    {
        return $this->hasOne(UserDriverDetail::class);
    }

    public function driverApplication()
    {
        return $this->hasOne(DriverApplication::class);
    }

    // Relación con carriers (managers de carriers)
    public function carriers()
    {
        return $this->belongsToMany(Carrier::class, 'user_carrier')
            ->withPivot('phone', 'job_position', 'photo', 'status')
            ->withTimestamps();
    }

    // Relación con Driver
    public function driver()
    {
        return $this->hasOne(UserDriverDetail::class);
    }

    //Registro de Media Library
    public function getProfilePhotoUrlAttribute()
    {
        // Si el usuario pertenece a un UserCarrier, busca en la colección "profile_photo_carrier"
        if ($this->carrierDetails()->exists()) {
            $media = $this->getFirstMedia('profile_photo_carrier');
        } else {
            // Si no, busca en la colección "profile_photos" (para superadmin o User estándar)
            $media = $this->getFirstMedia('profile_photos');
        }

        Log::info('Recuperando foto del User', [
            'user_id' => $this->id,
            'collection' => $this->carrierDetails()->exists() ? 'profile_photo_carrier' : 'profile_photos',
            'media_exists' => $media ? true : false,
            'media_url' => $media ? $media->getUrl() : null,
        ]);

        return $media ? $media->getUrl() : asset('build/default_profile.png');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile_photos')
            ->useDisk('public'); // Asegúrate de usar el disco público
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('webp')
            ->format('webp')
            ->keepOriginalImageFormat();
    }
    public function getMediaDirectoryAttribute(): string
    {
        return "users/{$this->id}/";
    }

    public function getMediaFileNameAttribute(): string
    {
        return "{$this->name}.webp";
    }

    public function getRouteKeyName()
    {
        return 'id';
    }


    // Relación con carriers asignados
    public function assignedCarriers()
    {
        return $this->belongsToMany(Carrier::class, 'user_carrier_access');
    }

    

}
