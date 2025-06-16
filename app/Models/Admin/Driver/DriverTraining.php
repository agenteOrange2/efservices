<?php

namespace App\Models\Admin\Driver;

use App\Models\User;
use App\Models\Admin\Driver\Training;
use App\Models\UserDriverDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DriverTraining extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_driver_detail_id',
        'training_id',
        'assigned_date',
        'due_date',
        'completed_date',
        'status', // 'assigned', 'in_progress', 'completed', 'overdue'
        'assigned_by',
        // 'completed_by', // Este campo no existe en la tabla
        'completion_notes',
    ];

    protected $casts = [
        'assigned_date' => 'datetime',
        'due_date' => 'datetime',
        'completed_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtener el conductor al que está asignado este entrenamiento
     */
    public function driver()
    {
        return $this->belongsTo(UserDriverDetail::class, 'user_driver_detail_id');
    }

    /**
     * Obtener el entrenamiento asignado
     */
    public function training()
    {
        return $this->belongsTo(Training::class);
    }

    /**
     * Obtener el usuario que asignó el entrenamiento
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
    
    /**
     * Obtener el usuario que completó el entrenamiento
     * NOTA: Esta relación está comentada porque el campo 'completed_by' no existe en la tabla
     */
    /*
    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
    */

    /**
     * Verificar si el entrenamiento está vencido
     */
    public function isOverdue()
    {
        if (!$this->due_date) {
            return false;
        }

        if ($this->status === 'completed') {
            return false;
        }

        return now()->gt($this->due_date);
    }

    /**
     * Marcar el entrenamiento como completado
     */
    public function markAsCompleted($notes = null, $userId = null)
    {
        $this->status = 'completed';
        $this->completed_date = now();
        
        if ($notes) {
            $this->completion_notes = $notes;
        }
        
        // No usamos completed_by porque no existe en la tabla
        // if ($userId) {
        //     $this->completed_by = $userId;
        // }
        
        return $this->save();
    }
}
