<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationRecipient extends Model
{
    protected $fillable = [
        'notification_type',
        'recipient_type',
        'user_id',
        'email',
        'name',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Relación con el usuario
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForNotificationType($query, $type)
    {
        return $query->where('notification_type', $type);
    }

    // Métodos de utilidad
    public function getRecipientEmailAttribute()
    {
        return $this->recipient_type === 'user' && $this->user 
            ? $this->user->email 
            : $this->email;
    }

    public function getRecipientNameAttribute()
    {
        return $this->recipient_type === 'user' && $this->user 
            ? $this->user->name 
            : $this->name;
    }
}
