<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Membership extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    
    protected $fillable = [
        'name',
        'description',
        'price',
        'max_carrier',
        'max_drivers',
        'max_vehicles',        
        'status',
    ];
    //Relación con los transportistas
    public function carriers()
    {  
        return $this->hasMany(Carrier::class, 'id_plan');
    }


    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image_membership')
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
        return "membership/{$this->id}/";
    }

    public function getMediaFileNameAttribute(): string
    {
        return "{$this->name}.webp";
    }
}
