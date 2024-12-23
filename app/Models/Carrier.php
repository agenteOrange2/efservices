<?php

namespace App\Models;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;


class Carrier extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;


    protected $fillable = [
        'name',
        'slug',
        'address',
        'state',
        'zipcode',
        'ein_number',
        'dot_number',
        'mc_number',
        'state_dot',
        'ifta_account',
        'logo_img',
        'id_plan',
        'status',
    ];

    // Boot para generar el referrer_token y slug automáticamente
    protected static function booted()
    {
        static::creating(function ($carrier) {
            $carrier->referrer_token = $carrier->referrer_token ?? Str::random(16);
            $carrier->slug = $carrier->slug ?? Str::slug($carrier->name);
        });
    
        // static::created(function ($carrier) {
        //     $documentTypes = DocumentType::all();
    
        //     foreach ($documentTypes as $documentType) {
        //         // Crear el CarrierDocument
        //         $carrierDocument = CarrierDocument::create([
        //             'carrier_id' => $carrier->id,
        //             'document_type_id' => $documentType->id,
        //             'status' => CarrierDocument::STATUS_PENDING,
        //             'date' => now(),
        //         ]);
    
        //         // Verificar si el DocumentType tiene un archivo predeterminado
        //         if ($documentType->getFirstMedia('default_documents')) {
        //             $media = $documentType->getFirstMedia('default_documents');
    
        //             // Asegúrate de que el archivo predeterminado exista
        //             if (file_exists($media->getPath())) {
        //                 $carrierDocument->addMedia($media->getPath())
        //                     ->usingFileName(strtolower(str_replace(' ', '_', $documentType->name)) . '.' . $media->extension)
        //                     ->toMediaCollection('carrier_documents');
        //             } else {
        //                 Log::error("El archivo predeterminado para el DocumentType {$documentType->name} no existe.");
        //             }
        //         }
        //     }
        // });
    }
    
    // Constantes para los valores de status
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_PENDING = 2;

    // Relación con usuarios (manager)
    public function managers()
    {
        return $this->belongsToMany(User::class, 'user_carrier')
            ->withPivot('status')
            ->withTimestamps();
    }

    // Relación con documentos
    public function documents()
    {
        return $this->hasMany(CarrierDocument::class);
    }

    public function userCarriers()
    {
        return $this->hasMany(UserCarrier::class, 'carrier_id');
    }

    public function membership()
    {
        return $this->belongsTo(Membership::class, 'id_plan');
    }

    // Método de acceso para obtener el nombre del status
    public function getStatusNameAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_PENDING => 'Pending',
            default => 'Unknown',
        };
    }

    public function generateReferrerToken(): void
    {
        $this->referrer_token = Str::random(16);
        $this->save();
    }



    //Media library
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo_carrier')
            ->useDisk('public') // Asegúrate de usar el disco público
            ->singleFile(); // Solo permite un archivo por colección
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('webp')
            ->format('webp')
            ->keepOriginalImageFormat();
    }
    public function getMediaDirectoryAttribute(): string
    {
        return "carrier/{$this->id}/";
    }

    public function getMediaFileNameAttribute(): string
    {
        return "{$this->name}.webp";
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    // Relación con usuarios asignados
    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'user_carrier_access');
    }
}
