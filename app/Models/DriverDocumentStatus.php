<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DriverDocumentStatus extends Model
{
    use HasUuids;

    protected $table = 'driver_document_status';

    protected $fillable = [
        'driver_id',
        'media_id',
        'category',
        'status',
        'expiry_date',
        'notes'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'driver_id' => 'integer',
        'media_id' => 'integer'
    ];

    /**
     * Get the driver that owns this document status
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(UserDriverDetail::class, 'driver_id');
    }

    /**
     * Get the media file associated with this document
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    /**
     * Get the category information
     */
    public function categoryInfo(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'category', 'name');
    }

    /**
     * Scope to get documents by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get expired documents
     */
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now())->where('status', '!=', 'expired');
    }

    /**
     * Scope to get documents expiring soon (within 30 days)
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->whereBetween('expiry_date', [now(), now()->addDays($days)]);
    }
}
