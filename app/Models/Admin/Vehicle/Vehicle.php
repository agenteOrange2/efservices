<?php
namespace App\Models\Admin\Vehicle;

use App\Models\Carrier;
use App\Models\Admin\Driver\DriverApplicationDetail;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverInspection;
use App\Models\EmergencyRepair;
use App\Models\Admin\Vehicle\VehicleDriverAssignment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Admin\Vehicle\VehicleMaintenance;

class Vehicle extends Model
{
    use HasFactory;
    
    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_PENDING = 'pending';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_OUT_OF_SERVICE = 'out_of_service';
    
    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return \Database\Factories\VehicleFactory::new();
    }

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
    
    
    /**
     * Relación con los mantenimientos del vehículo.
     */
    public function maintenances(): HasMany
    {
        return $this->hasMany(VehicleMaintenance::class);
    }

    /**
     * Relationship with emergency repairs of the vehicle.
     */
    public function emergencyRepairs(): HasMany
    {
        return $this->hasMany(EmergencyRepair::class);
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

    
    /**
     * Relación con todas las asignaciones de conductores del vehículo.
     */
    public function driverAssignments(): HasMany
    {
        return $this->hasMany(VehicleDriverAssignment::class);
    }
    
    /**
     * Relación con la asignación activa de conductor del vehículo.
     */
    public function activeDriverAssignment(): HasOne
    {
        return $this->hasOne(VehicleDriverAssignment::class)->where('status', 'active');
    }
    
    /**
     * Relación con la asignación actual de conductor del vehículo (activa o pendiente).
     */
    public function currentDriverAssignment(): HasOne
    {
        return $this->hasOne(VehicleDriverAssignment::class)
            ->whereIn('status', ['active', 'pending'])
            ->orderBy('created_at', 'desc');
    }
    
    /**
     * Relación con el historial de asignaciones de conductores del vehículo.
     */
    public function assignmentHistory(): HasMany
    {
        return $this->hasMany(VehicleDriverAssignment::class)->orderBy('created_at', 'desc');
    }
    
    /**
     * Obtener el conductor actualmente asignado al vehículo.
     */
    public function currentDriver()
    {
        return $this->activeDriverAssignment()?->user();
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