<?php

namespace App;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;
use App\Models\Admin\Driver\DriverAccident;

class CustomPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        // Si es un documento de accidente
        if ($media->model_type === DriverAccident::class) {
            $driverId = $media->model->userDriverDetail->id ?? 'undefined';
            return "driver/{$driverId}/accidents/{$media->id}/";
        }
        
        // Comportamiento predeterminado para otros tipos de modelos
        return $media->id . '/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media) . 'conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media) . 'responsive/';
    }
}
