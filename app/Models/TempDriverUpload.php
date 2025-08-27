<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TempDriverUpload extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'session_id',
        'file_type',
        'original_name',
        'temp_path',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime'
    ];

    /**
     * Define media collections for license files
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('license_front')
            ->acceptsMimeTypes(['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'])
            ->singleFile();

        $this->addMediaCollection('license_back')
            ->acceptsMimeTypes(['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'])
            ->singleFile();
    }

    /**
     * Define media conversions for thumbnails and previews
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(200)
            ->sharpen(10)
            ->quality(85)
            ->performOnCollections('license_front', 'license_back');

        $this->addMediaConversion('preview')
            ->width(800)
            ->height(600)
            ->quality(90)
            ->performOnCollections('license_front', 'license_back');
    }

    /**
     * Scope to get non-expired uploads
     */
    public function scopeNotExpired($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope to get expired uploads
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Scope to get uploads by session
     */
    public function scopeBySession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Check if the upload is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Get the preview URL for the uploaded file
     */
    public function getPreviewUrl(): ?string
    {
        $media = $this->getFirstMedia($this->file_type);
        return $media ? $media->getUrl('preview') : null;
    }

    /**
     * Get the thumbnail URL for the uploaded file
     */
    public function getThumbnailUrl(): ?string
    {
        $media = $this->getFirstMedia($this->file_type);
        return $media ? $media->getUrl('thumb') : null;
    }

    /**
     * Get file information
     */
    public function getFileInfo(): array
    {
        $media = $this->getFirstMedia($this->file_type);
        
        if (!$media) {
            return [];
        }

        return [
            'size' => $media->size,
            'mime_type' => $media->mime_type,
            'file_name' => $media->file_name,
            'original_name' => $this->original_name,
            'dimensions' => $media->getCustomProperty('dimensions'),
            'url' => $media->getUrl(),
            'preview_url' => $this->getPreviewUrl(),
            'thumbnail_url' => $this->getThumbnailUrl()
        ];
    }
}