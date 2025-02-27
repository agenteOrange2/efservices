<?php

namespace App\Models\Admin\Driver;

use App\Models\UserDriverDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DriverTrainingSchool extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_driver_detail_id',
        'date_start',
        'date_end',
        'school_name',
        'city',
        'state',
        'phone_number',
        'graduated',
        'subject_to_safety_regulations',
        'performed_safety_functions',
        'training_skills',
    ];

    protected $casts = [
        'date_start' => 'date',
        'date_end' => 'date',
        'graduated' => 'boolean',
        'subject_to_safety_regulations' => 'boolean',
        'performed_safety_functions' => 'boolean',
        'training_skills' => 'array',
    ];

    public function userDriverDetail()
    {
        return $this->belongsTo(UserDriverDetail::class);
    }

        /**
     * Define las colecciones de medios para este modelo.
     */
    public function registerMediaCollections(): void
    {
        // Colección para el certificado escolar - puede tener múltiples certificados
        $this->addMediaCollection('school_certificates');
        
        // Alternativamente, si solo necesitas un certificado por escuela:
        // $this->addMediaCollection('school_certificate')->singleFile();
    }

}