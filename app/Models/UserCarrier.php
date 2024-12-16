<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable; // Para autenticar
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;


class UserCarrier extends Authenticatable implements HasMedia
{
    use HasFactory, Notifiable, InteractsWithMedia;

    protected $fillable = [
        'carrier_id',
        'name',
        'email',
        'password',
        'phone',
        'job_position',
        'status',
        'photo',
    ];

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
    protected static function booted()
    {
        static::creating(function ($userCarrier) {
            if ($userCarrier->isDirty('password')) {
                $userCarrier->password = bcrypt($userCarrier->password);
            }
        });
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
