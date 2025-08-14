<?php

namespace App\Models\Admin\Driver;

use App\Models\Carrier;
use App\Models\UserDriverDetail;
use App\Traits\HasDocuments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DriverAccident extends Model implements HasMedia
{
    use HasFactory, HasDocuments, InteractsWithMedia;

    /**
     * Este método garantiza la integridad de los datos de accidentes
     * al eliminar los documentos asociados cuando se elimina un accidente
     */
    protected static function boot()
    {
        parent::boot();
        
        // Cuando se elimina un accidente, eliminar también sus documentos
        static::deleting(function (DriverAccident $accident) {
            $accident->deleteAllDocuments();
            // Eliminar también los archivos de media library
            $accident->clearMediaCollection('accident-images');
        });
    }

    protected $fillable = [
        'user_driver_detail_id',
        'accident_date',
        'nature_of_accident',
        'had_fatalities',
        'had_injuries',
        'number_of_fatalities',
        'number_of_injuries',
        'comments',
    ];

    protected $casts = [
        'accident_date' => 'date',
        'had_fatalities' => 'boolean',
        'had_injuries' => 'boolean',
        'number_of_fatalities' => 'integer',
        'number_of_injuries' => 'integer',
    ];

    public function userDriverDetail()
    {
        return $this->belongsTo(UserDriverDetail::class);
    }
    
    /**
     * Obtener el carrier asociado al accidente del conductor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }
    
    /**
     * Registra las colecciones de medios disponibles
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('accident-images')
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
                return "driver/{$driverId}/accidents/{$this->model->id}/";
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
     * 
     * @param \Spatie\MediaLibrary\MediaCollections\Models\Media|null $media
     */
    public function registerMediaConversions(\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        // Este método es necesario para la interfaz HasMedia
    }
    
    /**
     * Elimina una imagen de forma segura sin borrar el modelo
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
            // Logs para depuración - Mostrar todos los datos del registro media
            \Illuminate\Support\Facades\Log::info('Detalles del registro media a eliminar (accidente)', [
                'media_id' => $mediaId,
                'datos_completos' => $mediaRecord,
            ]);
            
            // Construir rutas basadas en diferentes posibilidades de cómo Spatie almacena los archivos
            $diskName = $mediaRecord->disk;
            
            // Posibilidad 1: Directamente el archivo en la ubicación personalizada
            $customPath = $mediaRecord->custom_properties['generated_conversions'] ?? null;
            if ($customPath) {
                \Illuminate\Support\Facades\Log::info('Intentando eliminar usando custom_properties', [
                    'custom_path' => $customPath
                ]);
            }
            
            // La ruta tradicional que contiene el archivo principal
            $filePath = $mediaRecord->id . '/' . $mediaRecord->file_name;
            \Illuminate\Support\Facades\Log::info('Ruta tradicional de archivo', ['path' => $filePath]);
            
            // Verificar si existe el archivo usando la ruta tradicional
            $exists = \Illuminate\Support\Facades\Storage::disk($diskName)->exists($filePath);
            \Illuminate\Support\Facades\Log::info('¿Existe el archivo en ruta tradicional?', ['exists' => $exists ? 'Sí' : 'No']);
            
            // Ruta alternativa: usar directamente file_name en la carpeta personalizada
            $path = ($mediaRecord->collection_name === 'accident-images')
                ? "driver/{$this->user_driver_detail_id}/accidents/{$this->id}/{$mediaRecord->file_name}"
                : $filePath;
                
            $existsAlt = \Illuminate\Support\Facades\Storage::disk($diskName)->exists($path);
            \Illuminate\Support\Facades\Log::info('Ruta alternativa y verificación', [
                'ruta_alternativa' => $path,
                'existe' => $existsAlt ? 'Sí' : 'No'
            ]);
            
            // Intentar eliminar usando ambas rutas
            if ($exists) {
                \Illuminate\Support\Facades\Log::info('Eliminando archivo usando ruta tradicional', ['path' => $filePath]);
                \Illuminate\Support\Facades\Storage::disk($diskName)->delete($filePath);
            }
            
            if ($existsAlt && $path !== $filePath) {
                \Illuminate\Support\Facades\Log::info('Eliminando archivo usando ruta alternativa', ['path' => $path]);
                \Illuminate\Support\Facades\Storage::disk($diskName)->delete($path);
            }
            
            // Eliminar directorio del media si existe (para limpiar completamente)
            $dirPath = $mediaRecord->id;
            if (\Illuminate\Support\Facades\Storage::disk($diskName)->exists($dirPath)) {
                \Illuminate\Support\Facades\Log::info('Eliminando directorio del media', ['dir' => $dirPath]);
                \Illuminate\Support\Facades\Storage::disk($diskName)->deleteDirectory($dirPath);
            }
            
            // También intentar eliminar la carpeta personalizada
            $customDir = "driver/{$this->user_driver_detail_id}/accidents/{$this->id}";
            if (\Illuminate\Support\Facades\Storage::disk($diskName)->exists($customDir)) {
                \Illuminate\Support\Facades\Log::info('Eliminando directorio personalizado', ['dir' => $customDir]);
                \Illuminate\Support\Facades\Storage::disk($diskName)->deleteDirectory($customDir);
            }
        }
        
        // Finalmente eliminamos el registro de la base de datos
        $result = DB::table('media')->where('id', $mediaId)->delete();
        \Illuminate\Support\Facades\Log::info('Registro eliminado de la base de datos', ['success' => $result ? 'Sí' : 'No']);
        
        return $result;
    }
}