<?php
namespace App\Models\Admin\Vehicle;

use App\Models\Carrier;
use App\Models\Admin\Driver\DriverApplicationDetail;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverInspection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'carrier_id',
        'make',
        'model',
        'type',
        'company_unit_number',
        'year',
        'vin',
        'gvwr',
        'registration_state',
        'registration_number',
        'registration_expiration_date',
        'permanent_tag',
        'tire_size',
        'fuel_type',
        'irp_apportioned_plate',
        'ownership_type',
        'driver_type',
        'location',
        'user_driver_detail_id',
        'annual_inspection_expiration_date',
        'out_of_service',
        'out_of_service_date',
        'suspended',
        'suspended_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'permanent_tag' => 'boolean',
        'irp_apportioned_plate' => 'boolean',
        'out_of_service' => 'boolean',
        'suspended' => 'boolean',
        'registration_expiration_date' => 'date',
        'annual_inspection_expiration_date' => 'date',
        'out_of_service_date' => 'date',
        'suspended_date' => 'date',
        'driver_type' => 'string',
    ];

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(UserDriverDetail::class, 'user_driver_detail_id');
    }

    public function serviceItems(): HasMany
    {
        return $this->hasMany(VehicleMaintenance::class);
    }

    public function vehicleMake(): BelongsTo
    {
        return $this->belongsTo(VehicleMake::class, 'make', 'name');
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class, 'type', 'name');
    }
    
    public function documents(): HasMany
    {
        return $this->hasMany(VehicleDocument::class);
    }
    
    /**
     * Relación con las inspecciones de este vehículo.
     */
    public function driverInspections(): HasMany
    {
        return $this->hasMany(\App\Models\Admin\Driver\DriverInspection::class);
    }
    
    /**
     * Relación con los detalles de aplicación del conductor (para información de propietario/tercero).
     */
    public function driverApplicationDetail(): HasOne
    {
        return $this->hasOne(\App\Models\Admin\Driver\DriverApplicationDetail::class);
    }
    
    /**
     * Obtener el estado actual del vehículo.
     */
    public function getStatusAttribute(): string
    {
        // Si existe un valor explícito en el campo status, lo usamos
        if (!empty($this->attributes['status'])) {
            return $this->attributes['status'];
        }
        
        // Si no, calculamos el estado basado en los campos existentes
        if ($this->out_of_service) {
            return 'out_of_service';
        }
        
        if ($this->suspended) {
            return 'suspended';
        }
        
        return 'active';
    }
    
    /**
     * Verificar si el vehículo está activo.
     */
    public function isActive(): bool
    {
        return !$this->out_of_service && !$this->suspended;
    }
    
    /**
     * Scope para filtrar vehículos activos.
     */
    public function scopeActive($query)
    {
        return $query->where('out_of_service', false)
                     ->where('suspended', false);
    }
    
    /**
     * Scope para filtrar vehículos fuera de servicio.
     */
    public function scopeOutOfService($query)
    {
        return $query->where('out_of_service', true);
    }
    
    /**
     * Scope para filtrar vehículos suspendidos.
     */
    public function scopeSuspended($query)
    {
        return $query->where('suspended', true);
    }
    
    /**
     * Scope para filtrar vehículos por tipo de conductor.
     */
    public function scopeByDriverType($query, $driverType)
    {
        return $query->where('driver_type', $driverType);
    }
    
    /**
     * Scope para filtrar vehículos de Owner Operator.
     */
    public function scopeOwnerOperator($query)
    {
        return $query->where('driver_type', 'owner_operator');
    }
    
    /**
     * Scope para filtrar vehículos de Third Party.
     */
    public function scopeThirdParty($query)
    {
        return $query->where('driver_type', 'third_party');
    }
    
    /**
     * Scope para filtrar vehículos de la compañía.
     */
    public function scopeCompany($query)
    {
        return $query->where('driver_type', 'company');
    }
    
    /**
     * Scope para filtrar vehículos sin asignar.
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('user_driver_detail_id');
    }
}