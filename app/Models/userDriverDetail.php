<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\Driver\DriverLicense;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Models\Admin\Driver\DriverApplication;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class UserDriverDetail extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($driver) {
            $lastNumber = static::where('carrier_id', $driver->carrier_id)
                ->max('driver_number') ?? 0;
            $driver->driver_number = sprintf('%03d', $lastNumber + 1); // Formato 001, 002, etc.
        });
    }

    protected $fillable = [
        'driver_number',
        'user_id',
        'carrier_id',
        'middle_name',
        'last_name',
        'date_of_birth',
        'license_number',
        'state_of_issue',
        'phone',
        'date_of_birth',
        'status',
        'terms_accepted',
        'confirmation_token',
    ];

    protected $casts = [
        'status' => 'integer',
        'terms_accepted' => 'boolean'
    ];

    // Constantes para los valores de status
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_PENDING = 2;

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
        return $media ? $media->getUrl() : asset('build/default_profile.png');
    }

    //Media library
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile_photo_driver')
            ->useDisk('public')
            ->singleFile();
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('webp')
            ->format('webp')
            ->keepOriginalImageFormat();
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
}
