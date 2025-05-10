<?php

namespace App\Models;

use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Admin\Driver\DriverApplication;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ThirdPartyDetail extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'driver_application_id',
        'vehicle_id',
        'third_party_name',
        'third_party_phone',
        'third_party_email',
        'third_party_dba',
        'third_party_address',
        'third_party_contact',
        'third_party_fein',        
        'email_sent',
    ];
    
    protected $casts = [
        'email_sent' => 'boolean',
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
