<?php

namespace App\Models\Admin\Vehicle;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class VehicleMaintenance extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vehicle_id',
        'unit',
        'service_date',
        'next_service_date',
        'service_tasks',
        'vendor_mechanic',
        'description',
        'cost',
        'odometer',
        'status',
    ];

    /**
     * Los atributos que deben ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'service_date' => 'datetime',
        'next_service_date' => 'datetime',
        'cost' => 'decimal:2',
        'status' => 'boolean',
    ];

    /**
     * Obtiene el vehículo al que pertenece este mantenimiento.
     *
     * @return BelongsTo
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Determina si el mantenimiento está completado.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status;
    }

    /**
     * Determina si la fecha del próximo mantenimiento está vencida.
     *
     * @return bool
     */
    public function isOverdue(): bool
    {
        return $this->next_service_date && $this->next_service_date->isPast();
    }

    /**
     * Determina si el próximo mantenimiento está cercano (dentro de los próximos 15 días).
     *
     * @return bool
     */
    public function isUpcoming(int $days = 15): bool
    {
        if (!$this->next_service_date) {
            return false;
        }

        return $this->next_service_date->isFuture() && 
               $this->next_service_date->diffInDays(now()) <= $days;
    }

    /**
     * Scope para filtrar mantenimientos completados.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope para filtrar mantenimientos pendientes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', false);
    }

    /**
     * Scope para filtrar mantenimientos vencidos.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOverdue($query)
    {
        return $query->where('next_service_date', '<', now())
                    ->where('status', false);
    }

    /**
     * Scope para filtrar próximos mantenimientos.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUpcoming($query, int $days = 15)
    {
        return $query->where('next_service_date', '>=', now())
                    ->where('next_service_date', '<=', now()->addDays($days))
                    ->where('status', false);
    }
    
    /**
     * Registra las colecciones de medios disponibles para este modelo.
     *
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('maintenance_files')
             ->useDisk('public')
             ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg']);
    }
}