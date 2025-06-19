<?php

namespace App\Support;

use App\Models\Admin\Driver\DriverEmploymentCompany;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class EmploymentVerificationPathGenerator implements PathGenerator
{
    /**
     * Genera la ruta donde se guardará el archivo de media.
     * Asegura que los archivos se guarden en el directorio del conductor correcto
     * usando el user_id del conductor asociado con la verificación de empleo.
     *
     * @param Media $media
     * @return string
     */
    public function getPath(Media $media): string
    {
        $userId = $this->getUserId($media);
        
        return "driver/{$userId}/employment_verifications/{$media->id}/";
    }

    /**
     * Genera la ruta donde se guardará la conversión del archivo.
     *
     * @param Media $media
     * @return string
     */
    public function getPathForConversions(Media $media): string
    {
        $userId = $this->getUserId($media);
        
        return "driver/{$userId}/employment_verifications/{$media->id}/conversions/";
    }

    /**
     * Genera la ruta donde se guardarán los archivos responsive images.
     *
     * @param Media $media
     * @return string
     */
    public function getPathForResponsiveImages(Media $media): string
    {
        $userId = $this->getUserId($media);
        
        return "driver/{$userId}/employment_verifications/{$media->id}/responsive-images/";
    }

    /**
     * Obtiene el ID de usuario del conductor asociado con la verificación de empleo.
     *
     * @param Media $media
     * @return int
     */
    protected function getUserId(Media $media): int
    {
        if ($media->model_type === DriverEmploymentCompany::class) {
            $employmentCompany = DriverEmploymentCompany::with('userDriverDetail.user')->find($media->model_id);
            
            if ($employmentCompany && $employmentCompany->userDriverDetail && $employmentCompany->userDriverDetail->user) {
                return $employmentCompany->userDriverDetail->user->id;
            }
        }
        
        // Si no se puede determinar el ID del usuario, usar un directorio genérico
        return 0;
    }
}
