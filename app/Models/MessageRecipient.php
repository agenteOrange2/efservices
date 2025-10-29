<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MessageRecipient extends Model
{
    protected $fillable = [
        'message_id',
        'recipient_type',
        'recipient_id',
        'email',
        'name',
        'delivery_status',
        'delivered_at',
        'read_at'
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'created_at' => 'datetime'
    ];

    /**
     * Get the message this recipient belongs to
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(AdminMessage::class, 'message_id');
    }

    /**
     * Get the recipient model (polymorphic relationship)
     */
    public function recipient(): MorphTo
    {
        return $this->morphTo('recipient', 'recipient_type', 'recipient_id');
    }

    /**
     * Scope to filter by delivery status
     */
    public function scopeByDeliveryStatus($query, $status)
    {
        return $query->where('delivery_status', $status);
    }

    /**
     * Scope to filter by recipient type
     */
    public function scopeByRecipientType($query, $type)
    {
        return $query->where('recipient_type', $type);
    }

    /**
     * Mark as delivered
     */
    public function markAsDelivered()
    {
        $this->update([
            'delivery_status' => 'delivered',
            'delivered_at' => now()
        ]);
    }

    /**
     * Mark as read
     */
    public function markAsRead()
    {
        $this->update([
            'read_at' => now()
        ]);
    }

    /**
     * Get delivery status badge color
     */
    public function getDeliveryStatusColorAttribute()
    {
        return match($this->delivery_status) {
            'delivered' => 'green',
            'sent' => 'blue',
            'failed' => 'red',
            'bounced' => 'orange',
            'pending' => 'gray',
            default => 'gray'
        };
    }
}
