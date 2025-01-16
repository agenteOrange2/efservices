<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotificationType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description'
    ];

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function preferences()
    {
        return $this->hasMany(NotificationPreference::class);
    }
}