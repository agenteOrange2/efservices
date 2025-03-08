<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;


class DocumentType extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;


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

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('default_documents')->useDisk('public');
    }

}
