<?php

namespace App\Models;

use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\VehicleDriverAssignment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OwnerOperatorDetail extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'assignment_id',
        'owner_name',
        'owner_phone',
        'owner_email',
        'contract_agreed',
        'notes'
    ];
    
    protected $casts = [
        'contract_agreed' => 'boolean',
    ];
    

    
    /**
     * Obtener la asignación de conductor asociada a este detalle.
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(VehicleDriverAssignment::class, 'assignment_id');
    }
    
    /**
     * Scope para buscar por nombre que contenga el texto dado.
     */
    public function scopeWhereNameContains($query, $name)
    {
        return $query->where('owner_name', 'like', '%' . $name . '%');
    }
    
    /**
     * Obtener el nombre del conductor.
     */
    public function getDriverName()
    {
        return $this->owner_name;
    }
    
    /**
     * Obtener el teléfono del conductor.
     */
    public function getDriverPhone()
    {
        return $this->owner_phone;
    }
    
    /**
     * Obtener el email del conductor.
     */
    public function getDriverEmail()
    {
        return $this->owner_email;
    }
    
    /**
     * Scope para obtener asignaciones activas.
     */
    public function scopeActive($query)
    {
        return $query->whereHas('assignment', function ($q) {
            $q->where('status', 'active');
        });
    }
    
    /**
     * Formatear información de contacto.
     */
    public function getFormattedContactInfo()
    {
        $contact = $this->owner_name;
        if ($this->owner_phone) {
            $contact .= ' - ' . $this->owner_phone;
        }
        if ($this->owner_email) {
            $contact .= ' (' . $this->owner_email . ')';
        }
        return $contact;
    }
}
