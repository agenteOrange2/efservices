<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Laravel\Jetstream\HasProfilePhoto;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Para autenticar


class UserCarrier extends Authenticatable implements HasMedia
{    
    use HasRoles, HasFactory, Notifiable, InteractsWithMedia;

    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use InteractsWithMedia;

    

    protected $fillable = [
        'carrier_id',
        'name',
        'email',
        'password',
        'phone',
        'job_position',
        'status',        
        'confirmation_token',
    ];

    protected $dates = ['created_at', 'updated_at'];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected static function booted()
    {
        static::created(function ($userCarrier) {
            // Asignar automáticamente el rol al crear un usuario
            $userCarrier->assignRole('user_carrier');
        });
    }

    // Constantes para los valores de status
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_PENDING = 2;

    // Método de acceso para obtener el nombre del status
    public function getStatusNameAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_PENDING => 'Pending',
            default => 'Unknown',
        };
    }

    // Relación con Carrier
    public function carrier()
    {
        return $this->belongsTo(Carrier::class, 'carrier_id');
    }

    // Encriptar contraseña automáticamente
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // Usa el sistema automático de hashing.
        ];
    }

    //Para la relacion del usuario al registrarse con el carrier
    public function scopeFromReferrer($query, string $token)
    {
        return $query->whereHas('carrier', function ($query) use ($token) {
            $query->where('referrer_token', $token);
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
    
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }


    public function getProfilePhotoUrlAttribute()
    {
        $media = $this->getFirstMedia('profile_photo_carrier');
        return $media ? $media->getUrl() : asset('build/default_profile.png'); // Ruta predeterminada si no hay foto
    }


    //Media library
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile_photo_carrier')
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
        return "userCarrier/{$this->id}/";
    }

    public function getMediaFileNameAttribute(): string
    {
        return "{$this->name}.webp";
    }


}
