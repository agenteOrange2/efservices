<?php

namespace App\Models\Admin\Driver;

use App\Models\UserDriverDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\MediaCollections\Events\CollectionHasBeenCleared;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAdded;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenRemoved;

class DriverAccident extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    
    /**
     * Desactivar completamente la eliminación en cascada de Spatie
     */
    public $deleteMedia = false;
    
    /**
     * Sobreescribir el método de la interfaz HasMedia para evitar eliminación de modelos
     */
    public function shouldDeletePreservingMedia(): bool 
    {
        return false;
    }
    
    /**
     * Sobreescribir el método registerMediaConversions para indicar que no queremos conversiones
     */
    public function registerMediaConversions(Media $media = null): void
    {
        // No queremos conversiones, dejamos vacío
    }
    
    /**
     * Modificar completamente el comportamiento de boot para interceptar cualquier intento de eliminación
     */
    protected static function boot()
    {
        parent::boot();
        
        // Loggear cuando se está iniciando el modelo
        Log::info('DriverAccident boot method called');
        
        // Desactivar eventos que podrían causar eliminación en cascada
        static::deleting(function (DriverAccident $accident) {
            if ($accident->isForceDeleting()) {
                // Si se está forzando la eliminación, permitir que continue pero desvincular los medias
                $accident->media()->update(['model_type' => 'deleted_document', 'model_id' => 0]);
                Log::warning('Eliminando accidente forzosamente, medias desvinculados', ['accident_id' => $accident->id]);
                return true;
            }
            
            // Solo permitir la eliminación si es explícitamente solicitada por el usuario en el controller destroy
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
            $calledFromDestroyDocument = false;
            
            foreach ($trace as $item) {
                if (isset($item['function']) && $item['function'] === 'destroyDocument') {
                    // Si viene de destroyDocument, DETENER la eliminación del accidente
                    Log::warning('Intento de eliminar accidente desde destroyDocument - BLOQUEADO', ['accident_id' => $accident->id]);
                    return false;
                }
                
                if (isset($item['function']) && $item['function'] === 'destroy') {
                    // Si es el método destroy del controller (eliminación explícita), permitir
                    Log::warning('Eliminación explícita del accidente permitida', ['accident_id' => $accident->id]);
                    return true;
                }
            }
            
            // Por defecto, BLOQUEAR cualquier intento de eliminación no explícito
            Log::warning('Intento de eliminación no autorizado del accidente - BLOQUEADO', ['accident_id' => $accident->id]);
            return false;
        });
    }
    
    /**
     * Sobreescribir el método para desactivar eliminación en cascada
     * Esta función es parte de la API de Spatie MediaLibrary
     */
    public function deletePreservingMedia(): bool
    {
        // Siempre retornar false para que no elimine el modelo cuando se elimina un media
        Log::info('deletePreservingMedia llamado en accidente ' . $this->id);
        return false;
    }
    
    /**
     * Asegurar que la eliminación de media no afecte al modelo
     */
    protected function canDeleteMedia(Media $media): bool
    {
        // Esta es otra forma de controlar la eliminación
        Log::info('canDeleteMedia llamado para media ' . $media->id);
        return false;
    }
    
    /**
     * Define la ruta y configuraciones para los archivos multimedia
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('accident_documents')
             ->useDisk('public');
    }

    protected $fillable = [
        'user_driver_detail_id',
        'accident_date',
        'nature_of_accident',
        'had_injuries',
        'number_of_injuries',
        'had_fatalities',
        'number_of_fatalities',
        'comments',
    ];

    protected $casts = [
        'accident_date' => 'date',
        'had_injuries' => 'boolean',
        'had_fatalities' => 'boolean',
    ];

    public function userDriverDetail()
    {
        return $this->belongsTo(UserDriverDetail::class);
    }
}