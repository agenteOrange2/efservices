<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'carrier_id',
        'event_type',
        'step',
        'recipients',
        'status',
        'error_message',
        'data',
        'sent_at'
    ];

    protected $casts = [
        'recipients' => 'array',
        'data' => 'array',
        'sent_at' => 'datetime'
    ];

    /**
     * Get the user that owns the notification log
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the carrier that owns the notification log
     */
    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    /**
     * Mark notification as sent
     *
     * @return void
     */
    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now()
        ]);
    }

    /**
     * Mark notification as failed
     *
     * @param string $errorMessage
     * @return void
     */
    public function markAsFailed(string $errorMessage)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage
        ]);
    }

    /**
     * Get failed notifications
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function failed()
    {
        return static::where('status', 'failed');
    }

    /**
     * Get sent notifications
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function sent()
    {
        return static::where('status', 'sent');
    }

    /**
     * Get pending notifications
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function pending()
    {
        return static::where('status', 'pending');
    }
}