<?php
namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Models\Admin\Driver\DriverApplication;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class UserDriverDetail extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'carrier_id',
        'middle_name',
        'last_name',
        'suffix',
        'phone',
        'license_number',
        'state_of_issue',
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
    
}