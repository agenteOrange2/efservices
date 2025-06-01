<?php

namespace App\Models\Admin\Driver;

use App\Models\UserDriverDetail;
use App\Models\Admin\Vehicle\Vehicle;
use App\Traits\HasDocuments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DriverInspection extends Model
{
    use HasFactory, HasDocuments;

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
        
        // Crear la ruta siguiendo el patrón solicitado: driver/{id}/inspections/{id}/
        $path = "driver/{$driverId}/inspections/{$this->id}";
        
        return $fileName ? "{$path}/{$fileName}" : $path;
    }
}
