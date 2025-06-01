<?php

namespace App\Models\Admin\Driver;

use App\Models\UserDriverDetail;
use App\Traits\HasDocuments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class DriverCourse extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasDocuments;

    protected $fillable = [
        'user_driver_detail_id',
        'organization_name',
        'phone',
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
     * Registra colecciones de medios
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('certificates')
            ->useDisk('public');
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
}
