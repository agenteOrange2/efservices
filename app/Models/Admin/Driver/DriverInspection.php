<?php

namespace App\Models\Admin\Driver;

use App\Models\UserDriverDetail;
use App\Models\Admin\Vehicle\Vehicle;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DriverInspection extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_driver_detail_id',
        'vehicle_id',
        'inspection_date',
        'inspection_type',
        'inspector_name',
        'location',
        'status',
        'defects_found',
        'corrective_actions',
        'is_defects_corrected',
        'defects_corrected_date',
        'corrected_by',
        'is_vehicle_safe_to_operate',
        'notes',
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'defects_corrected_date' => 'date',
        'is_defects_corrected' => 'boolean',
        'is_vehicle_safe_to_operate' => 'boolean',
    ];

    /**
     * Get the driver detail that owns the inspection.
     */
    public function userDriverDetail()
    {
        return $this->belongsTo(UserDriverDetail::class);
    }

    /**
     * Get the vehicle associated with the inspection.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Define media collections for this model.
     */
    public function registerMediaCollections(): void
    {
        // Collection for inspection reports
        $this->addMediaCollection('inspection_reports');
        
        // Collection for defect photos
        $this->addMediaCollection('defect_photos');
        
        // Collection for repair documentation
        $this->addMediaCollection('repair_documents');
    }

    /**
     * Register media conversions.
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('webp')
            ->format('webp')
            ->keepOriginalImageFormat();
        
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(200);
    }
}
