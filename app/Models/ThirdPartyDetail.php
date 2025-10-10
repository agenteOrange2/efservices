<?php

namespace App\Models;

use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\VehicleDriverAssignment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ThirdPartyDetail extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'assignment_id',
        'third_party_name',
        'third_party_phone',
        'third_party_email',
        'third_party_dba',
        'third_party_address',
        'third_party_contact',
        'third_party_fein',
        'email_sent',
        'notes'
    ];
    
    protected $casts = [
        'email_sent' => 'boolean',
    ];
    

    
    /**
     * Obtener la asignación de conductor asociada a este detalle.
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(VehicleDriverAssignment::class, 'assignment_id');
    }
}
