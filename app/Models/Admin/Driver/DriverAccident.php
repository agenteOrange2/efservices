<?php

namespace App\Models\Admin\Driver;

use App\Models\UserDriverDetail;
use App\Traits\HasDocuments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverAccident extends Model
{
    use HasFactory, HasDocuments;

    /**
     * Este método garantiza la integridad de los datos de accidentes
     * al eliminar los documentos asociados cuando se elimina un accidente
     */
    protected static function boot()
    {
        parent::boot();
        
        // Cuando se elimina un accidente, eliminar también sus documentos
        static::deleting(function (DriverAccident $accident) {
            $accident->deleteAllDocuments();
        });
    }

    protected $fillable = [
        'user_driver_detail_id',
        'accident_date',
        'nature_of_accident',
        'had_injuries',
        'number_of_injuries',
        'had_fatalities',
        'number_of_fatalities',
        'comments',
    ];

    protected $casts = [
        'accident_date' => 'date',
        'had_injuries' => 'boolean',
        'had_fatalities' => 'boolean',
    ];

    public function userDriverDetail()
    {
        return $this->belongsTo(UserDriverDetail::class);
    }
}