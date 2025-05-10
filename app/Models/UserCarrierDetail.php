<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class UserCarrierDetail extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'carrier_id',
        'phone',
        'job_position',
        'status',
        'confirmation_token',
    ];

    protected $casts = [
        'status' => 'integer'
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

    /* 
    * Relación con el modelo User
    */

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /*
    * Relación con el modelo Carrier.
    */

    public function carrer()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class, 'carrier_id', 'id');
    }
    
    // En el modelo UserCarrierDetail
    public function getRouteKeyName()
    {
        return 'id'; // O la columna que estás utilizando para identificar el modelo
    }

    public function getProfilePhotoUrlAttribute()
    {
        $media = $this->getFirstMedia('profile_photo_carrier');
        Log::info('Recuperando foto del UserCarrier', [
            'user_carrier_id' => $this->id,
            'media_exists' => $media ? true : false,
            'media_url' => $media ? $media->getUrl() : null,
        ]);
        return $media ? $media->getUrl() : asset('build/default_profile.png');
    }
    
    
    
    //Media library
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile_photo_carrier')
        ->useDisk('public')
        ->singleFile();    
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
