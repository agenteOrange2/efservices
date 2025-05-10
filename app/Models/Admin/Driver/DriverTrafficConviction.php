<?php

namespace App\Models\Admin\Driver;

use App\Models\UserDriverDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\MediaLibrary\CustomPathGenerator;

class DriverTrafficConviction extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_driver_detail_id',
        'carrier_id',
        'conviction_date',
        'location',
        'charge',
        'penalty',
        'conviction_type',
        'description'
    ];

    protected static function boot()
    {
        parent::boot();

        // Evitar que se elimine el registro cuando se elimina el último medio
        static::deleting(function ($model) {
            $model->media()->each(function ($media) {
                $media->delete();
            });
            return true;
        });
    }

    protected $casts = [
        'conviction_date' => 'date',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('traffic-tickets')
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
            ->usePathGenerator(new CustomPathGenerator());
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(100)
            ->height(100)
            ->performOnCollections('traffic-tickets');
    }

    public function userDriverDetail()
    {
        return $this->belongsTo(UserDriverDetail::class);
    }
}