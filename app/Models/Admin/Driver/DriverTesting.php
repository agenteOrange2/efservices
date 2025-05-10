<?php

namespace App\Models\Admin\Driver;

use App\Models\UserDriverDetail;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DriverTesting extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_driver_detail_id',
        'test_date',
        'test_type',
        'test_result',
        'administered_by',
        'location',
        'notes',
        'next_test_due',
        'is_random_test',
        'is_post_accident_test',
        'is_reasonable_suspicion_test',
    ];

    protected $casts = [
        'test_date' => 'date',
        'next_test_due' => 'date',
        'is_random_test' => 'boolean',
        'is_post_accident_test' => 'boolean',
        'is_reasonable_suspicion_test' => 'boolean',
    ];

    /**
     * Get the driver detail that owns the test.
     */
    public function userDriverDetail()
    {
        return $this->belongsTo(UserDriverDetail::class);
    }

    /**
     * Define media collections for this model.
     */
    public function registerMediaCollections(): void
    {
        // Collection for test results documents
        $this->addMediaCollection('test_documents');
        
        // Collection for test certificates
        $this->addMediaCollection('test_certificates');
    }

    /**
     * Register media conversions.
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('webp')
            ->format('webp')
            ->keepOriginalImageFormat();
    }
}
