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
    ];

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
}
