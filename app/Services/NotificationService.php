<?php

namespace App\Services;

use App\Models\User;
use App\Models\NotificationType;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function notifyAdminsOfNewCarrier(User $newUser, string $message)
    {
        // Obtener el tipo de notificación para nuevo carrier
        $notificationType = NotificationType::where('name', 'new_carrier_registration')->first();
        
        // Obtener todos los admins
        $admins = User::role('superadmin')->get();

        foreach ($admins as $admin) {
            // Crear la notificación en la base de datos
            Notification::create([
                'user_id' => $admin->id,
                'notification_type_id' => $notificationType->id,
                'message' => $message,
                'sent_at' => now(),
                'is_read' => false
            ]);
        }
    }

    public function createNotificationForMultipleUsers(Collection $users, string $type, string $message)
    {
        $notificationType = NotificationType::where('name', $type)->first();
        
        if (!$notificationType) {
            return null;
        }

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'notification_type_id' => $notificationType->id,
                'message' => $message,
                'sent_at' => now(),
                'is_read' => false
            ]);
        }
    }
    public function createNotification(User $user, string $type, string $message)
    {
        $notificationType = NotificationType::where('name', $type)->first();
        
        if (!$notificationType) {
            return null;
        }

        return Notification::create([
            'user_id' => $user->id,
            'notification_type_id' => $notificationType->id,
            'message' => $message,
            'sent_at' => now(),
            'is_read' => false
        ]);
    }

    public function markAsRead(int $notificationId): bool
    {
        return Notification::where('id', $notificationId)
            ->update(['is_read' => true]);
    }

    public function getUnreadNotifications(int $userId)
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->orderBy('sent_at', 'desc')
            ->get();
    }
}