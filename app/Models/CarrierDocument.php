<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CarrierDocument extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'carrier_id',
        'document_type_id',
        'filename',
        'date',
        'notes',
        'status',
    ];

    // Definir los campos que deben ser convertidos a instancias Carbon
    protected $casts = [
        'date' => 'date',
    ];

    public const STATUS_PENDING = 0;
    public const STATUS_APPROVED = 1;
    public const STATUS_REJECTED = 2;
    public const STATUS_IN_PROCESS = 3;

    // Relación con el transportista
    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    // Relación con el tipo de documento
    public function documentType()
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }
    
    public function getStatusNameAttribute(): string
    {
        // Si el status es null o no válido, asumimos que está pendiente
        if ($this->status === null) {
            return 'Pending';
        }
        
        // Convertir a entero para asegurar la comparación correcta
        $status = (int) $this->status;
        
        return match ($status) {
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_PROCESS => 'In Process',
            default => 'Pending', // Por defecto, asumimos que está pendiente en lugar de Unknown
        };
    }

    // Configuración de Media Library
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('carrier_documents')
            ->useDisk('public') // Usar el disco público
            ->singleFile(); // Solo un archivo por colección
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('webp')
            ->format('webp')
            ->keepOriginalImageFormat();
    }
}
