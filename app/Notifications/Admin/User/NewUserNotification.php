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
            ->subject('Welcome to EF Services')
            ->greeting('Hello ' . $this->user->name . '!')
            ->line('Your account has been successfully created.')
            ->line('Your access credentials are:')
            ->line('Email: ' . $this->user->email)
            ->line('Password: ' . $this->password)
            ->action('Log In', url('/login'))
            ->line('Please change your password after logging in for the first time.')
            ->line('Thank you for joining us!');
    }
}
