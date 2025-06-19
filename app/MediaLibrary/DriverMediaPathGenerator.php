<?php

namespace App\MediaLibrary;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class DriverMediaPathGenerator implements PathGenerator
{
    /**
     * Get the path for the given media, relative to the root storage path.
     *
     * @param \Spatie\MediaLibrary\MediaCollections\Models\Media $media
     *
     * @return string
     */
    public function getPath(Media $media): string
    {
        $driverId = null;
        
        // Si el modelo es un UserDriverDetail o tiene relación con un driver
        if (method_exists($media->model, 'getUserDriverId')) {
            $driverId = $media->model->getUserDriverId();
        } elseif (property_exists($media->model, 'user_id')) {
            $driverId = $media->model->user_id;
        } elseif (property_exists($media->model, 'driver_id')) {
            $driverId = $media->model->driver_id;
        }
        
        if (!$driverId) {
            return "drivers/unknown/{$media->id}/";
        }
        
        return "drivers/{$driverId}/{$media->collection_name}/{$media->id}/";
    }

    /**
     * Get the path for conversions of the given media, relative to the root storage path.
     *
     * @param \Spatie\MediaLibrary\MediaCollections\Models\Media $media
     *
     * @return string
     */
    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media) . 'conversions/';
    }

    /**
     * Get the path for responsive images of the given media, relative to the root storage path.
     *
     * @param \Spatie\MediaLibrary\MediaCollections\Models\Media $media
     *
     * @return string
     */
    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media) . 'responsive/';
    }
}
