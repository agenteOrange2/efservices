<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'requirement'
    ];


    //Relación con los documentos de transportistas
    public function carrierDocuments()
    {
        return $this->hasMany(CarrierDocument::class);
    }

    public function scopeRequired($query)
    {
        return $query->where('requirement', true);
    }
}
