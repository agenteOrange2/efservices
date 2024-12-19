<?php

namespace App\MediaLibrary;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class CustomPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        $model = $media->model;

        if ($model instanceof \App\Models\User) {
            return "users/{$model->id}/";
        }

        if ($model instanceof \App\Models\Membership) {
            return "memberships/{$model->id}/";
        }

        if ($model instanceof \App\Models\Carrier) {
            return "carriers/{$model->id}/";
        }

        if ($model instanceof \App\Models\UserCarrier) {
            return "user_carriers/{$model->id}/";
        }

        if ($model instanceof \App\Models\CarrierDocument) {
            $carrierName = strtolower(str_replace(' ', '_', $model->carrier->name));
            $documentTypeName = strtolower(str_replace(' ', '_', $model->documentType->name));
    
            return "carrier_document/{$carrierName}/{$documentTypeName}/";
        }
    
        if ($model instanceof \App\Models\DocumentType) {
            $documentTypeName = strtolower(str_replace(' ', '_', $model->name));
            return "carrier_document/default/{$documentTypeName}/";
        }

        return "others/{$model->id}/";
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
