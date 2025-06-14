<?php

namespace App\Models\Admin\Driver;

use App\Models\User;
use App\Models\Admin\Driver\DriverTraining;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Training extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'title',
        'description',
        'content_type', // 'file', 'video', 'url'
        'video_url',
        'status', // 'active', 'inactive'
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtener el usuario que creó el entrenamiento
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Obtener las asignaciones de conductores para este entrenamiento
     */
    public function driverAssignments()
    {
        return $this->hasMany(DriverTraining::class);
    }

    /**
     * Registra las colecciones de medios para este modelo
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('training_files')
            ->useDisk('public')
            ->acceptsMimeTypes([
                'image/jpeg', 
                'image/png', 
                'image/gif', 
                'application/pdf', 
                'application/msword', 
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'video/mp4',
                'video/quicktime',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            ]);
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
                return "trainings/{$this->model->id}/";
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
     * Elimina un archivo de forma segura sin borrar el modelo
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
