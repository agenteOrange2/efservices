<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\UserDriverDetail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class VehicleVerificationToken extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'token',
        'driver_application_id',
        'vehicle_id',
        'third_party_name',
        'third_party_email',
        'third_party_phone',
        'verified',
        'verified_at',
        'signature_data',
        'signature_path',
        'document_path',
        'expires_at',
    ];
    


    protected $casts = [
        'verified' => 'boolean',
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the driver application that owns the token.
     */
    public function driverApplication(): BelongsTo
    {
        return $this->belongsTo(DriverApplication::class);
    }

    /**
     * Get the vehicle that is being verified.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Check if the token has expired.
     */
    public function isExpired(): bool
    {
        return now()->gt($this->expires_at);
    }

    /**
     * Generate a new verification token.
     */
    public static function generateToken(): string
    {
        return md5(uniqid() . time());
    }
    
    /**
     * Register media collections for the model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('signature')
            ->singleFile()
            ->useDisk('public');
    }
    
    /**
     * Register media conversions for the model.
     */
    public function registerMediaConversions(\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(100)
            ->sharpen(10);
    }
}
