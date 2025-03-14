<?php

namespace App\MediaLibrary;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class CustomPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        $model = $media->model;

        if ($model instanceof \App\Models\UserCarrierDetail) {
            // Almacena específicamente en `user_carrier/{id}`
            return "user_carrier/{$model->id}/";
        }

        if ($model instanceof \App\Models\UserDriverDetail) {
            // Almacena específicamente en `user_carrier/{id}`
            return "driver/{$model->id}/";
        }

        if ($model instanceof \App\Models\User) {
            // Verificar si el usuario tiene un UserCarrierDetail relacionado
            if ($model->carrierDetails()->exists()) {
                return "user_carrier/{$model->id}/";
            }

            // Default para usuarios "superadmin" u otros
            return "users/{$model->id}/";
        }

        if ($model instanceof \App\Models\Membership) {
            return "memberships/{$model->id}/";
        }

        if ($model instanceof \App\Models\Carrier) {
            return "carriers/{$model->id}/";
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

        // Añadir rutas para los nuevos modelos
        if ($model instanceof \App\Models\Admin\Driver\DriverLicense) {
            $driverId = $model->driverDetail->id ?? 'unknown';
            return "driver/{$driverId}/licenses/";
        }

        if ($model instanceof \App\Models\Admin\Driver\DriverMedicalQualification) {
            $driverId = $model->driverDetail->id ?? 'unknown';
            return "driver/{$driverId}/medical/";
        }

        if ($model instanceof \App\Models\Admin\Driver\DriverTrainingSchool) {
            $driverId = $model->userDriverDetail->id ?? 'unknown';
            return "driver/{$driverId}/training_schools/";
        }

        if ($model instanceof \App\Models\Admin\Driver\DriverCertification) {
            $driverId = $model->userDriverDetail->id ?? 'unknown';
            return "driver/{$driverId}/certification/";
        }

        if ($model instanceof \App\Models\Admin\Driver\DriverApplication) {
            // Tratar de obtener el ID del conductor de diferentes maneras
            $driverId = null;
            
            // Intentar obtener por relación user->userDriverDetail
            if ($model->user && $model->user->userDriverDetail) {
                $driverId = $model->user->userDriverDetail->id;
            } 
            // Si no funciona, intentar encontrar el UserDriverDetail por user_id
            else if ($model->user_id) {
                $userDriverDetail = \App\Models\UserDriverDetail::where('user_id', $model->user_id)->first();
                if ($userDriverDetail) {
                    $driverId = $userDriverDetail->id;
                }
            }
            
            // Si aún no tenemos ID, usar un valor por defecto
            if (!$driverId) {
                $driverId = 'unknown';
                // Registrar error para depuración
                \Illuminate\Support\Facades\Log::warning('No se pudo determinar el ID del conductor para la aplicación', [
                    'driver_application_id' => $model->id,
                    'user_id' => $model->user_id ?? 'null'
                ]);
            }
            
            // Verificamos el nombre de la colección para determinar donde guardar
            if ($media->collection_name === 'application_pdf') {
                // El PDF completo se guarda en la raíz de driver/{id}/
                return "driver/{$driverId}/";
            }
            
            // PDFs individuales por paso se guardan en una subcarpeta
            return "driver/{$driverId}/driver_applications/";
        }

        return "others/{$model->getKey()}/";
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