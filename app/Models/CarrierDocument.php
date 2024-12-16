<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CarrierDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'carrier_id',
        'document_type_id',
        'filename',
        'date',
        'notes',
        'status', // Nuevo campo
    ];

    public const STATUS_PENDING = 0;
    public const STATUS_APPROVED = 1;
    public const STATUS_REJECTED = 2;

    // Relación con el transportista
    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    // Relación con el tipo de documento
    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }


    public function getStatusNameAttribute(): string
{
    return match ($this->status) {
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_PENDING => 'Pending',
        default => 'Unknown',
    };
}
}
