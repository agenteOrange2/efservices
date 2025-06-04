<?php

namespace App\Models\Admin\Driver;

use App\Models\UserDriverDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasDocuments;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Storage;

class DriverTrafficConviction extends Model implements HasMedia
{
    use HasFactory, HasDocuments, InteractsWithMedia;

    protected $fillable = [
        'user_driver_detail_id',
        'carrier_id',
        'conviction_date',
        'location',
        'charge',
        'penalty',
        'conviction_type',
        'description'
    ];

    /**
     * Este método garantiza la integridad de los datos de infracciones
     * al eliminar los documentos asociados cuando se elimina una infracción
     */
    protected static function boot()
    {
        parent::boot();
        
        // Cuando se elimina una infracción, eliminar también sus documentos
        static::deleting(function (DriverTrafficConviction $conviction) {
            $conviction->deleteAllDocuments();
        });
    }

    protected $casts = [
        'conviction_date' => 'date',
    ];

    /**
     * Define los tipos de archivo aceptados para las infracciones de tráfico
     * 
     * @return array
     */
    public static function acceptedMimeTypes(): array
    {
        return [
            'image/jpeg', 
            'image/png', 
            'application/pdf', 
            'application/msword', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
    }

    public function userDriverDetail()
    {
        return $this->belongsTo(UserDriverDetail::class);
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
        
        // Crear la ruta siguiendo el patrón solicitado: driver/{id}/traffic/{id}/
        $path = "driver/{$driverId}/traffic/{$this->id}";
        
        return $fileName ? "{$path}/{$fileName}" : $path;
    }
    
    /**
     * Registra las colecciones de medios disponibles
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('traffic-images')
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
                return "driver/{$driverId}/traffic_convictions/{$this->model->id}/";
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
            \Illuminate\Support\Facades\Log::info('Detalles del registro media a eliminar', [
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
            $path = ($mediaRecord->collection_name === 'traffic-images')
                ? "driver/{$this->user_driver_detail_id}/traffic_convictions/{$this->id}/{$mediaRecord->file_name}"
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
            $customDir = "driver/{$this->user_driver_detail_id}/traffic_convictions/{$this->id}";
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