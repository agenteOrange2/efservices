<?php

namespace App\Models\Admin\Driver;

use App\Models\UserDriverDetail;
use App\Models\Admin\Vehicle\Vehicle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DriverInspection extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_driver_detail_id',
        'vehicle_id',
        'inspection_date',
        'inspection_type',
        'inspector_name',
        'location',
        'status',
        'defects_found',
        'corrective_actions',
        'is_defects_corrected',
        'defects_corrected_date',
        'corrected_by',
        'is_vehicle_safe_to_operate',
        'notes',
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'defects_corrected_date' => 'date',
        'is_defects_corrected' => 'boolean',
        'is_vehicle_safe_to_operate' => 'boolean',
    ];

    /**
     * Get the driver detail that owns the inspection.
     */
    public function userDriverDetail()
    {
        return $this->belongsTo(UserDriverDetail::class);
    }

    /**
     * Get the vehicle associated with the inspection.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Define el valor por defecto para la colección de documentos
     * 
     * @return string
     */
    public function getDefaultDocumentCollection(): string
    {
        return 'inspection_documents';
    }
    
    /**
     * Registra colecciones de medios
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('inspection_documents')
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
                return "driver/{$driverId}/inspections/{$this->model->id}/";
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
     * Registra las conversiones de medios para generar miniaturas
     * cuando se suben imágenes al modelo.
     * 
     * @param Media $media
     * @return void
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
             ->width(200)
             ->height(200)
             ->sharpen(10)
             ->nonQueued()
             ->performOnCollections('inspection_documents');
             
        $this->addMediaConversion('preview')
             ->width(400)
             ->height(300)
             ->sharpen(10)
             ->nonQueued()
             ->performOnCollections('inspection_documents');
    }
    
    /**
     * Elimina un documento de forma segura sin borrar el modelo
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
