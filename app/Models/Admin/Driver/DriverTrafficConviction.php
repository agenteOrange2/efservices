<?php

namespace App\Models\Admin\Driver;

use App\Models\UserDriverDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasDocuments;

class DriverTrafficConviction extends Model
{
    use HasFactory, HasDocuments;

    protected $fillable = [
        'user_driver_detail_id',
        'carrier_id',
        'conviction_date',
        'location',
        'charge',
        'penalty',
        'conviction_type',
        'description'
    ];

    /**
     * Este método garantiza la integridad de los datos de infracciones
     * al eliminar los documentos asociados cuando se elimina una infracción
     */
    protected static function boot()
    {
        parent::boot();
        
        // Cuando se elimina una infracción, eliminar también sus documentos
        static::deleting(function (DriverTrafficConviction $conviction) {
            $conviction->deleteAllDocuments();
        });
    }

    protected $casts = [
        'conviction_date' => 'date',
    ];

    /**
     * Define los tipos de archivo aceptados para las infracciones de tráfico
     * 
     * @return array
     */
    public static function acceptedMimeTypes(): array
    {
        return [
            'image/jpeg', 
            'image/png', 
            'application/pdf', 
            'application/msword', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
    }

    public function userDriverDetail()
    {
        return $this->belongsTo(UserDriverDetail::class);
    }
}