<?php

namespace App\Models\Admin\Driver;

use Spatie\MediaLibrary\HasMedia; // Añadir esta interfaz
use App\Models\UserDriverDetail;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia; // Añadir este trait
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media; // Añade esta línea

class DriverTrainingSchool extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

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
     * Alias para userDriverDetail() para mayor consistencia en el código
     */
    public function driver()
    {
        return $this->userDriverDetail();
    }

    /**
     * Define las colecciones de medios para este modelo.
     */
    public function registerMediaCollections(): void
    {
        // Colección para documentos de la escuela de entrenamiento
        $this->addMediaCollection('training_files');
    }

}