<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class DocumentAttachment extends Model
{
    use HasFactory;
    
    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'file_path',
        'file_name',
        'original_name',
        'mime_type',
        'size',
        'collection',
        'custom_properties',
    ];
    
    /**
     * Los atributos que deben ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'custom_properties' => 'array',
        'size' => 'integer',
    ];
    
    /**
     * Obtiene el modelo al que pertenece este documento.
     */
    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }
    
    /**
     * Obtiene la URL para acceder al documento.
     */
    public function getUrl(): string
    {
        // Eliminar el prefijo 'public/' si existe en la ruta
        $cleanPath = preg_replace('/^public\//', '', $this->file_path);
        return asset('storage/' . $cleanPath);
    }
    
    /**
     * Determina si el documento es una imagen.
     */
    public function isImage(): bool
    {
        return in_array($this->mime_type, [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
        ]);
    }
    
    /**
     * Determina si el documento es un PDF.
     */
    public function isPdf(): bool
    {
        $mimeType = $this->mime_type ?? '';
        return $mimeType === 'application/pdf';
    }
    
    /**
     * Devuelve la ruta completa del archivo en el sistema de archivos.
     * 
     * @return string
     */
    public function getPath(): string
    {
        return Storage::disk('public')->path($this->file_path);
    }
}
