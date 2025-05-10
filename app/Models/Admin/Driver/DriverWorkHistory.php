<?php

namespace App\Models\Admin\Driver;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\UserDriverDetail;



class DriverWorkHistory extends Model
{
    use HasFactory;

    protected $table = 'driver_work_history'; // Importante: definimos la tabla exacta

    protected $fillable = [
        'user_driver_detail_id',
        'previous_company',
        'start_date',
        'end_date',
        'location',
        'position',
        'reason_for_leaving',
        'reference_contact'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    /**
     * Obtener el detalle del conductor asociado a este historial de trabajo.
     */
    public function userDriverDetail(): BelongsTo
    {
        return $this->belongsTo(UserDriverDetail::class, 'user_driver_detail_id');
    }
}