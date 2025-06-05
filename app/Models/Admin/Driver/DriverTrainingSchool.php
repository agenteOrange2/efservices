<?php

namespace App\Models\Admin\Driver;

use App\Models\UserDriverDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\MediaCollections\File;

class DriverTrainingSchool extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_driver_detail_id',
        'date_start',
        'date_end',
        'school_name',
        'city',
        'state',
        'phone_number',
        'graduated',
        'subject_to_safety_regulations',
        'performed_safety_functions',
        'training_skills',
    ];

    protected $casts = [
        'date_start' => 'date',
        'date_end' => 'date',
        'graduated' => 'boolean',
        'subject_to_safety_regulations' => 'boolean',
        'performed_safety_functions' => 'boolean',
        'training_skills' => 'array',
    ];

    public function userDriverDetail()
    {
        return $this->belongsTo(UserDriverDetail::class);
    }
    
    /**
     * Alias para userDriverDetail() para mayor consistencia en el código
     */
    public function driver()
    {
        return $this->userDriverDetail();
    }
    
    /**
     * Registra las colecciones de medios para este modelo
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('school_certificates')
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
    }
    
    /**
     * Define un generador de rutas personalizado para Media Library
     */
    public function getCustomMediaPathGenerator() 
    {
        return new class($this) extends \Spatie\MediaLibrary\Support\PathGenerator\PathGenerator {
            protected $model;
            
            public function __construct($model) 
            {
                $this->model = $model;
            }
            
            public function getPath(\Spatie\MediaLibrary\MediaCollections\Models\Media $media): string 
            {
                $driverId = $this->model->user_driver_detail_id;
                return "driver/{$driverId}/training_schools/{$this->model->id}/";
            }
            
            public function getPathForConversions(\Spatie\MediaLibrary\MediaCollections\Models\Media $media): string 
            {
                return $this->getPath($media) . 'conversions/';
            }
            
            public function getPathForResponsiveImages(\Spatie\MediaLibrary\MediaCollections\Models\Media $media): string 
            {
                return $this->getPath($media) . 'responsive/';
            }
        };
    }
    
    /**
     * Registra las conversiones de medios para el modelo.
     * 
     * @param Media $media
     * @return void
     */
    public function registerMediaConversions(Media $media = null): void
    {
        // No se crean conversiones adicionales, solo se guarda la imagen original
    }
    
    /**
     * Elimina un certificado de forma segura sin borrar el modelo
     * También elimina el archivo físico del disco
     *
     * @param int $mediaId ID del media a eliminar
     * @return bool Resultado de la operación
     */
    public function safeDeleteMedia($mediaId)
    {
        // Primero obtenemos la información del archivo para poder eliminarlo físicamente
        $mediaRecord = DB::table('media')->where('id', $mediaId)->first();
        
        if ($mediaRecord) {
            // Construir la ruta del archivo físico
            $diskName = $mediaRecord->disk;
            $filePath = $mediaRecord->id . '/' . $mediaRecord->file_name;
            
            // Log para depuración
            \Illuminate\Support\Facades\Log::info('Eliminando archivo físico', [
                'media_id' => $mediaId,
                'disk' => $diskName,
                'path' => $filePath
            ]);
            
            // Eliminar el archivo físico
            if (\Illuminate\Support\Facades\Storage::disk($diskName)->exists($filePath)) {
                \Illuminate\Support\Facades\Storage::disk($diskName)->delete($filePath);
            }
            
            // Eliminar directorio del media si existe (para limpiar completamente)
            $dirPath = $mediaRecord->id;
            if (\Illuminate\Support\Facades\Storage::disk($diskName)->exists($dirPath)) {
                \Illuminate\Support\Facades\Storage::disk($diskName)->deleteDirectory($dirPath);
            }
        }
        
        // Finalmente eliminamos el registro de la base de datos
        return DB::table('media')->where('id', $mediaId)->delete();
    }
}