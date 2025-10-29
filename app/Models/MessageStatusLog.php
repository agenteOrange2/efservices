<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageStatusLog extends Model
{
    protected $fillable = [
        'message_id',
        'status',
        'notes'
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];

    /**
     * Get the message this log belongs to
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(AdminMessage::class, 'message_id');
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get recent logs
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Create a new status log entry
     */
    public static function createLog($messageId, $status, $notes = null)
    {
        return static::create([
            'message_id' => $messageId,
            'status' => $status,
            'notes' => $notes
        ]);
    }
}
