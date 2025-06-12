<?php

namespace App\Models\Admin\Driver;

use App\Models\UserDriverDetail;
use App\Traits\HasDocuments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DriverCourse extends Model implements HasMedia
{
    use HasFactory, HasDocuments, InteractsWithMedia;

    protected $fillable = [
        'user_driver_detail_id',
        'organization_name',        
        'city',
        'state',
        'certification_date',
        'experience',
        'expiration_date',
    ];

    protected $casts = [
        'certification_date' => 'date',
        'expiration_date' => 'date',
    ];

    /**
     * Relación con los detalles del conductor
     */
    public function driverDetail()
    {
        return $this->belongsTo(UserDriverDetail::class, 'user_driver_detail_id');
    }


    
    /**
     * Define la ruta donde se guardarán los documentos.
     *
     * @param string $collection Nombre de la colección
     * @param string|null $fileName Nombre del archivo (opcional)
     * @return string Ruta relativa
     */
    protected function getDocumentPath(string $collection, ?string $fileName = null): string
    {
        // Obtener el ID del conductor desde la relación
        $driverId = $this->user_driver_detail_id ?? 'unknown';
        
        // Crear la ruta siguiendo el patrón solicitado: driver/{id}/courses/{id}/
        $path = "driver/{$driverId}/courses/{$this->id}";
        
        return $fileName ? "{$path}/{$fileName}" : $path;
    }
    
    /**
     * Registra las colecciones de medios disponibles
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('course_certificates')
            ->useDisk('public');
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
                return "driver/{$driverId}/courses/{$this->model->id}/";
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
     * Método que se ejecuta al agregar un archivo a media
     */
    public function registerMediaConversions(Media $media = null): void
    {
        // Este método es necesario para la interfaz HasMedia
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
