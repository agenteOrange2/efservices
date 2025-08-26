<?php

namespace App\MediaLibrary;

use App\Models\Admin\Driver\DriverDetail;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class CustomPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        $model = $media->model;
        $modelType = get_class($model);
        $collection = $media->collection_name;
        $customProperties = $media->custom_properties;

        \Illuminate\Support\Facades\Log::info('CustomPathGenerator called', [
            'model_type' => $modelType,
            'collection' => $collection,
            'custom_properties' => $customProperties,
            'media_id' => $media->id,
            'model_id' => $model->id ?? 'unknown'
        ]);

        if ($model instanceof \App\Models\UserCarrierDetail) {
            // Almacena específicamente en `user_carrier/{id}`
            return "user_carrier/{$model->id}/";
        }

        if ($model instanceof \App\Models\UserDriverDetail) {
            $driverId = $model->id ?? 'unknown';
            $collection = $media->collection_name;
            
            // Manejo específico para licencias con subdirectorios front/back
            if ($collection === 'license_front') {
                \Illuminate\Support\Facades\Log::info('CustomPathGenerator: Creando ruta para license_front', [
                    'driver_id' => $driverId,
                    'media_id' => $media->id,
                    'collection' => $collection
                ]);
                return "driver/{$driverId}/licenses/front/";
            } elseif ($collection === 'license_back') {
                \Illuminate\Support\Facades\Log::info('CustomPathGenerator: Creando ruta para license_back', [
                    'driver_id' => $driverId,
                    'media_id' => $media->id,
                    'collection' => $collection
                ]);
                return "driver/{$driverId}/licenses/back/";
            }
            
            // Default para otras colecciones
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

        // Ruta personalizada para archivos de entrenamientos
        if ($model instanceof \App\Models\Admin\Driver\Training) {
            return "trainings/{$model->id}/";
        }

        if ($model instanceof \App\Models\Admin\Driver\DriverMedicalQualification) {
            $driverId = $model->driverDetail->id ?? 'unknown';
            return "driver/{$driverId}/medical/";
        }

        if ($model instanceof \App\Models\Admin\Driver\DriverTrainingSchool) {
            $driverId = $model->userDriverDetail->id ?? 'unknown';
            $schoolId = $model->id;
            return "driver/{$driverId}/training_schools/{$schoolId}/";
        }

        // Lógica duplicada eliminada - UserDriverDetail se maneja arriba en el archivo
        
        if ($model instanceof \App\Models\Admin\Driver\DriverCourse) {
            $driverId = $model->driverDetail->id ?? 'unknown';
            $courseId = $model->id;
            return "driver/{$driverId}/courses/{$courseId}/";
        }

        if ($model instanceof \App\Models\Admin\Driver\DriverCertification) {
            $driverId = $model->userDriverDetail->id ?? 'unknown';
            $certificationId = $model->id;
            return "driver/{$driverId}/certification/{$certificationId}/";
        }
        
        // Ruta personalizada para documentos de verificación de empleo
        if ($model instanceof \App\Models\Admin\Driver\DriverEmploymentCompany) {
            // Obtener el ID del conductor a través de la relación
            $driverId = $model->user_driver_detail_id ?? 'unknown';
            return "driver/{$driverId}/employment_verification/{$model->id}/";
        }
        
        if ($model instanceof \App\Models\Admin\Driver\DriverTrafficConviction) {
            $driverId = $model->userDriverDetail->id ?? 'unknown';
            $convictionId = $model->id;
            return "driver/{$driverId}/traffic_convictions/{$convictionId}/";
        }
        
        if ($model instanceof \App\Models\Admin\Driver\DriverAccident) {
            $driverId = $model->userDriverDetail->id ?? 'unknown';
            $accidentId = $model->id;
            return "driver/{$driverId}/accidents/{$accidentId}/";
        }
        
        if ($model instanceof \App\Models\VehicleVerificationToken) {
            // Obtener el ID del conductor desde la aplicación del conductor
            $driverApplicationId = $model->driver_application_id;
            $driverApplication = \App\Models\Admin\Driver\DriverApplication::find($driverApplicationId);
            
            if ($driverApplication && $driverApplication->user_id) {
                // Buscar el UserDriverDetail asociado al usuario de la aplicación, sin importar su estado
                $userDriverDetail = \App\Models\UserDriverDetail::where('user_id', $driverApplication->user_id)->first();
                
                if ($userDriverDetail) {
                    $driverId = $userDriverDetail->id;
                    \Illuminate\Support\Facades\Log::info('CustomPathGenerator: Usando ID del conductor de la aplicación', [
                        'driver_application_id' => $driverApplicationId,
                        'user_id' => $driverApplication->user_id,
                        'driver_id' => $driverId,
                        'status' => $userDriverDetail->status ?? 'unknown'
                    ]);
                    return "driver/{$driverId}/vehicle_verifications/";
                } else {
                    // Si no existe un UserDriverDetail para este usuario, crear el directorio basado en el user_id
                    // Esto asegura que cuando se cree el UserDriverDetail, los documentos ya estarán en el directorio correcto
                    $userId = $driverApplication->user_id;
                    \Illuminate\Support\Facades\Log::info('CustomPathGenerator: No existe UserDriverDetail, usando user_id', [
                        'driver_application_id' => $driverApplicationId,
                        'user_id' => $userId
                    ]);
                    return "driver/user_{$userId}/vehicle_verifications/";
                }
            }
            
            // Si no se puede obtener el ID del conductor desde la aplicación, intentar obtenerlo del vehículo
            if ($model->vehicle && $model->vehicle->user_driver_detail_id) {
                $driverId = $model->vehicle->user_driver_detail_id;
                \Illuminate\Support\Facades\Log::info('CustomPathGenerator: Usando ID del conductor del vehículo', [
                    'vehicle_id' => $model->vehicle->id,
                    'driver_id' => $driverId
                ]);
                return "driver/{$driverId}/vehicle_verifications/";
            }
            
            // Si no se puede obtener el ID del conductor, usar el ID de la aplicación como fallback
            \Illuminate\Support\Facades\Log::info('CustomPathGenerator: Usando ID de la aplicación como fallback', [
                'driver_application_id' => $driverApplicationId
            ]);
            return "driver/application_{$driverApplicationId}/vehicle_verifications/";
        }

        // Gestionar archivos de inspecciones
        if ($model instanceof \App\Models\Admin\Driver\DriverInspection) {
            $driverId = $model->userDriverDetail->id ?? 'unknown';
            $vehicleId = $model->vehicle_id ?? 'none';
            
            // Organizar por tipo de colección
            if ($media->collection_name === 'inspection_reports') {
                return "driver/{$driverId}/inspections/{$model->id}/reports/";
            } else if ($media->collection_name === 'defect_photos') {
                return "driver/{$driverId}/inspections/{$model->id}/defects/";
            } else if ($media->collection_name === 'repair_documents') {
                return "driver/{$driverId}/inspections/{$model->id}/repairs/";
            }
            
            // Default para otras colecciones de inspección
            return "driver/{$driverId}/inspections/{$model->id}/";
        }
        
        // Gestionar archivos de mantenimiento de vehículos
        if ($model instanceof \App\Models\Admin\Vehicle\VehicleMaintenance) {
            $vehicleId = $model->vehicle_id ?? 'unknown';
            
            // Organizar por tipo de colección
            if ($media->collection_name === 'maintenance_files') {
                return "vehicle/{$vehicleId}/";
            }
            
            // Default para otras colecciones de mantenimiento
            return "vehicle/{$vehicleId}/";
        }
        
        // Gestionar documentos de vehículos
        if ($model instanceof \App\Models\Admin\Vehicle\VehicleDocument) {
            $vehicleId = $model->vehicle_id ?? 'unknown';
            return "vehicle/{$vehicleId}/documents/";
        }

        // Gestionar archivos de pruebas (testing)
        if ($model instanceof \App\Models\Admin\Driver\DriverTesting) {
            $driverId = $model->userDriverDetail->id ?? 'unknown';
            
            // Organizar por tipo de colección
            if ($media->collection_name === 'test_documents') {
                return "driver/{$driverId}/testing/{$model->id}/documents/";
            } else if ($media->collection_name === 'test_certificates') {
                return "driver/{$driverId}/testing/{$model->id}/certificates/";
            }
            
            // Default para otras colecciones de testing
            return "driver/{$driverId}/testing/{$model->id}/";
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

    /**
     * Genera nombres de archivo únicos para las licencias incluyendo el unique_id de la licencia
     */
    public function getPathForFile(Media $media): string
    {
        $model = $media->model;
        $collection = $media->collection_name;
        $customProperties = $media->custom_properties;
        
        // Solo aplicar nombres únicos para licencias de UserDriverDetail
        if ($model instanceof \App\Models\UserDriverDetail && 
            ($collection === 'license_front' || $collection === 'license_back')) {
            
            // Usar unique_id de las custom_properties si está disponible, sino usar driver_id como fallback
            $uniqueId = $customProperties['unique_id'] ?? $model->id;
            $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);
            
            if ($collection === 'license_front') {
                $fileName = "card_front_{$uniqueId}.{$extension}";
            } else {
                $fileName = "card_back_{$uniqueId}.{$extension}";
            }
            
            \Illuminate\Support\Facades\Log::info('CustomPathGenerator: Generando nombre único de archivo con unique_id', [
                'unique_id' => $uniqueId,
                'driver_id' => $model->id,
                'collection' => $collection,
                'original_name' => $media->file_name,
                'new_name' => $fileName
            ]);
            
            return $this->getPath($media) . $fileName;
        }
        
        // Para otros modelos, usar el comportamiento por defecto
        return $this->getPath($media) . $media->file_name;
    }
}