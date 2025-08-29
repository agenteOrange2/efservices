<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'step',
        'recipients',
        'is_active'
    ];

    protected $casts = [
        'recipients' => 'array',
        'is_active' => 'boolean'
    ];

    /**
     * Get notification setting for a specific event and step
     *
     * @param string $eventType
     * @param string|null $step
     * @return NotificationSetting|null
     */
    public static function getForEvent(string $eventType, ?string $step = null)
    {
        return static::where('event_type', $eventType)
            ->where('step', $step)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get all active notification settings
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActive()
    {
        return static::where('is_active', true)->get();
    }

    /**
     * Update or create notification setting
     *
     * @param string $eventType
     * @param string|null $step
     * @param array $recipients
     * @param bool $isActive
     * @return NotificationSetting
     */
    public static function updateOrCreateSetting(string $eventType, ?string $step, array $recipients, bool $isActive = true)
    {
        return static::updateOrCreate(
            ['event_type' => $eventType, 'step' => $step],
            ['recipients' => $recipients, 'is_active' => $isActive]
        );
    }
}