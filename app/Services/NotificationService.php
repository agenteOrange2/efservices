<?php

namespace App\Services;

use App\Models\User;
use App\Models\NotificationType;
use App\Models\Notification;
use App\Models\NotificationSetting;
use App\Models\NotificationLog;
use App\Models\NotificationRecipient;
use App\Models\Carrier;
use App\Mail\NewCarrierAdminNotification;
use App\Mail\AdminNotificationMail;
use App\Notifications\CarrierNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Notification as NotificationFacade;

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

    /**
     * Send notification when a carrier step is completed
     *
     * @param User $user
     * @param string $step
     * @param array $data
     * @return void
     */
    public function sendStepCompletedNotification(User $user, string $step, array $data = [])
    {
        // Get all recipients (superadmins + additional recipients)
        $recipients = $this->getAllRecipientsForNotification('user_carrier');
        
        if (empty($recipients)) {
            Log::info('No recipients found for step completed notification', [
                'user_id' => $user->id,
                'step' => $step
            ]);
            return;
        }

        $this->sendCarrierNotification($user, null, 'step_completed', $step, $recipients, $data);
    }

    /**
     * Send notification when carrier registration is completed
     *
     * @param User $user
     * @param Carrier $carrier
     * @param array $data
     * @return void
     */
    public function sendRegistrationCompletedNotification(User $user, Carrier $carrier, array $data = [])
    {
        // Get all recipients (superadmins + additional recipients)
        $recipients = $this->getAllRecipientsForNotification('carrier_registered');
        
        if (empty($recipients)) {
            Log::info('No recipients found for registration completed notification', [
                'user_id' => $user->id,
                'carrier_id' => $carrier->id
            ]);
            return;
        }

        $this->sendCarrierNotification($user, $carrier, 'registration_completed', null, $recipients, $data);
    }

    /**
     * Send carrier notification email and native Laravel notifications
     *
     * @param User $user
     * @param Carrier|null $carrier
     * @param string $eventType
     * @param string|null $step
     * @param array $recipients
     * @param array $data
     * @return void
     */
    private function sendCarrierNotification(User $user, ?Carrier $carrier, string $eventType, ?string $step, array $recipients, array $data = [])
    {
        // Create notification log
        $log = NotificationLog::create([
            'user_id' => $user->id,
            'carrier_id' => $carrier ? $carrier->id : null,
            'event_type' => $eventType,
            'step' => $step,
            'recipients' => $recipients,
            'status' => 'pending',
            'data' => $data
        ]);

        try {
            // Send email to each recipient
            foreach ($recipients as $recipient) {
                Mail::to($recipient)->queue(new NewCarrierAdminNotification($user, $carrier, $eventType, $step, $data));
            }

            // Send native Laravel notifications to admin users (for the bell icon)
            $this->sendNativeNotifications($user, $carrier, $eventType, $step, $data);

            $log->markAsSent();
            
            Log::info('Carrier notification sent successfully', [
                'log_id' => $log->id,
                'user_id' => $user->id,
                'carrier_id' => $carrier ? $carrier->id : null,
                'event_type' => $eventType,
                'step' => $step,
                'recipients_count' => count($recipients)
            ]);
        } catch (\Exception $e) {
            $log->markAsFailed($e->getMessage());
            
            Log::error('Failed to send carrier notification', [
                'log_id' => $log->id,
                'user_id' => $user->id,
                'carrier_id' => $carrier ? $carrier->id : null,
                'event_type' => $eventType,
                'step' => $step,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Send native Laravel notifications to configured recipients
     *
     * @param User $user
     * @param Carrier|null $carrier
     * @param string $eventType
     * @param string|null $step
     * @param array $data
     * @return void
     */
    private function sendNativeNotifications(User $user, ?Carrier $carrier, string $eventType, ?string $step, array $data = [])
    {
        // Get recipients based on notification type instead of all superadmins
        $recipients = NotificationRecipient::active()
            ->forNotificationType($eventType)
            ->get();
        
        // If no specific recipients configured, fall back to superadmins
        if ($recipients->isEmpty()) {
            $admins = User::role('superadmin')->get();
        } else {
            // Get users from recipients configuration
            $admins = collect();
            foreach ($recipients as $recipient) {
                if ($recipient->user_id) {
                    $user_obj = User::find($recipient->user_id);
                    if ($user_obj) {
                        $admins->push($user_obj);
                    }
                } else {
                    // For email-only recipients, try to find user by email
                    $user_obj = User::where('email', $recipient->email)->first();
                    if ($user_obj) {
                        $admins->push($user_obj);
                    }
                }
            }
        }
        
        // Prepare notification content based on event type
        $title = '';
        $message = '';
        
        switch ($eventType) {
            case 'step_completed':
                $title = 'Paso Completado';
                $message = "El usuario {$user->name} ha completado el paso: {$step}";
                break;
            case 'registration_completed':
                $title = 'Registro Completado';
                $message = "El carrier {" . ($carrier ? $carrier->company_name : $user->name) . "} ha completado su registro";
                break;
            case 'user_carrier':
                $title = 'Nueva Actividad de Carrier';
                $message = "Nueva actividad del carrier {$user->name}";
                break;
            default:
                $title = 'Nueva Notificación';
                $message = "Nueva actividad del usuario {$user->name}";
        }
        
        // Send notification to each configured recipient
        foreach ($admins as $admin) {
            $admin->notify(new CarrierNotification(
                $title,
                $message,
                'info',
                [
                    'user_id' => $user->id,
                    'carrier_id' => $carrier ? $carrier->id : null,
                    'event_type' => $eventType,
                    'step' => $step,
                    'data' => $data
                ]
            ));
        }
        
        // Send email notification to superadmin (frontend@kuiraweb.com)
        $this->sendEmailToSuperadmin($user, $carrier, $eventType, $step, $title, $message, $data);
    }
    
    /**
     * Send email notification to superadmin
     *
     * @param User $user
     * @param Carrier|null $carrier
     * @param string $eventType
     * @param string|null $step
     * @param string $title
     * @param string $message
     * @param array $data
     * @return void
     */
    private function sendEmailToSuperadmin(User $user, ?Carrier $carrier, string $eventType, ?string $step, string $title, string $message, array $data = [])
    {
        try {
            $adminEmail = config('app.admin_notification_email', env('ADMIN_NOTIFICATION_EMAIL', 'frontend@kuiraweb.com'));
            
            Mail::to($adminEmail)->queue(new AdminNotificationMail(
                $user,
                $carrier,
                $eventType,
                $step,
                $title,
                $message,
                $data
            ));
            
            Log::info('Admin email notification sent', [
                'admin_email' => $adminEmail,
                'event_type' => $eventType,
                'user_id' => $user->id,
                'title' => $title
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send admin email notification', [
                'error' => $e->getMessage(),
                'event_type' => $eventType,
                'user_id' => $user->id
            ]);
        }
    }

    /**
     * Get all recipients for a notification type (superadmins + additional recipients)
     *
     * @param string $notificationType
     * @return array
     */
    private function getAllRecipientsForNotification(string $notificationType): array
    {
        $recipients = [];
        
        // Always include superadmin emails
        $superadmins = User::role('superadmin')->get();
        foreach ($superadmins as $admin) {
            $recipients[] = $admin->email;
        }
        
        // Get additional recipients from notification_recipients table
        $additionalRecipients = NotificationRecipient::active()
            ->forNotificationType($notificationType)
            ->get();
            
        foreach ($additionalRecipients as $recipient) {
            $email = $recipient->email;
            if ($email && !in_array($email, $recipients)) {
                $recipients[] = $email;
            }
        }
        
        return array_unique($recipients);
    }

    /**
     * Get notification settings for management
     *
     * @return Collection
     */
    public function getNotificationSettings()
    {
        return NotificationSetting::all();
    }

    /**
     * Update notification setting
     *
     * @param string $eventType
     * @param string|null $step
     * @param array $recipients
     * @param bool $isActive
     * @return NotificationSetting
     */
    public function updateNotificationSetting(string $eventType, ?string $step, array $recipients, bool $isActive = true)
    {
        return NotificationSetting::updateOrCreateSetting($eventType, $step, $recipients, $isActive);
    }

    /**
     * Get notification logs with filters
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getNotificationLogs(array $filters = [])
    {
        $query = NotificationLog::with(['user', 'carrier']);

        if (isset($filters['event_type'])) {
            $query->where('event_type', $filters['event_type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    /**
     * Obtener estadísticas de notificaciones
     */
    public function getNotificationStats(): array
    {
        $total = NotificationLog::count();
        $sent = NotificationLog::where('status', 'sent')->count();
        $failed = NotificationLog::where('status', 'failed')->count();
        $pending = NotificationLog::where('status', 'pending')->count();
        $activeSettings = NotificationSetting::where('is_active', true)->count();

        return [
            'total' => $total,
            'sent' => $sent,
            'failed' => $failed,
            'pending' => $pending,
            'active_settings' => $activeSettings,
            'success_rate' => $total > 0 ? round(($sent / $total) * 100, 2) : 0
        ];
    }

    /**
     * Obtener detalles de un log específico
     */
    public function getNotificationLogDetails(int $logId): ?NotificationLog
    {
        return NotificationLog::find($logId);
    }

    /**
     * Reintentar una notificación fallida
     */
    public function retryFailedNotification(int $logId): bool
    {
        $log = NotificationLog::find($logId);
        
        if (!$log || $log->status !== 'failed') {
            return false;
        }

        try {
            // Decodificar los datos del log
            $data = json_decode($log->data, true) ?? [];
            $recipients = json_decode($log->recipients, true) ?? [];

            // Reenviar la notificación
            $this->sendCarrierNotification(
                $log->event_type,
                (object) ['id' => $log->user_id, 'name' => 'Usuario', 'email' => 'user@example.com'],
                (object) ['id' => $log->carrier_id, 'company_name' => 'Carrier'],
                $data,
                $recipients
            );

            return true;
        } catch (\Exception $e) {
            \Log::error('Error al reintentar notificación: ' . $e->getMessage());
            return false;
        }
    }
}