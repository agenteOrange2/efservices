<?php

namespace App\Models;

use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Admin\Driver\DriverApplication;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OwnerOperatorDetail extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'driver_application_id',
        'vehicle_id',
        'owner_name',
        'owner_phone',
        'owner_email',
        'contract_agreed',
    ];
    
    protected $casts = [
        'contract_agreed' => 'boolean',
    ];
    
    /**
     * Obtener la aplicación del conductor asociada a este detalle.
     */
    public function driverApplication(): BelongsTo
    {
        return $this->belongsTo(DriverApplication::class, 'driver_application_id');
    }
    
    /**
     * Obtener el vehículo asociado a este detalle.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
