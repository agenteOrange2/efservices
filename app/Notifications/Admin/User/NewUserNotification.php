<?php

namespace App\Notifications\Admin\User;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewUserNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $password;

    public function __construct(User $user, string $password)
    {
        $this->user = $user;
        $this->password = $password;
        Log::info('NewUserNotification constructor called', [
            'user_id' => $user->id,
            'user_email' => $user->email
        ]);
    }

    public function via($notifiable): array
    {
        // Solo usamos el canal de mail ya que tenemos nuestro propio sistema de notificaciones
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        Log::info('NewUserNotification toMail method called', [
            'notifiable_id' => $notifiable->id,
            'notifiable_email' => $notifiable->email
        ]);

        return (new MailMessage)
            ->subject('Bienvenido a EF Services')
            ->greeting('¡Hola ' . $this->user->name . '!')
            ->line('Tu cuenta ha sido creada exitosamente.')
            ->line('Tus credenciales de acceso son:')
            ->line('Email: ' . $this->user->email)
            ->line('Contraseña: ' . $this->password)
            ->action('Iniciar Sesión', url('/login'))
            ->line('Por favor, cambia tu contraseña después de iniciar sesión por primera vez.')
            ->line('¡Gracias por unirte a nosotros!');
    }
}
