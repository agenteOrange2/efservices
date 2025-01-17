<?php

namespace App\Notifications\Admin\User;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AdminNewUserCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $newUser;
    
    public function __construct(User $newUser)
    {
        $this->newUser = $newUser;
        Log::info('AdminNewUserCreatedNotification constructor called', [
            'new_user_id' => $newUser->id,
            'new_user_email' => $newUser->email
        ]);
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        Log::info('AdminNewUserCreatedNotification toMail method called', [
            'admin_email' => config('app.admin_email'),
            'new_user_details' => [
                'name' => $this->newUser->name,
                'email' => $this->newUser->email
            ]
        ]);

        return (new MailMessage)
            ->subject('Nuevo Usuario Administrador Creado')
            ->greeting('¡Hola Administrador!')
            ->line('Se ha creado un nuevo usuario administrador en el sistema.')
            ->line('Detalles del nuevo usuario:')
            ->line('Nombre: ' . $this->newUser->name)
            ->line('Email: ' . $this->newUser->email)
            ->line('Fecha de creación: ' . $this->newUser->created_at->format('d/m/Y H:i'))
            ->action('Ver Usuario', route('admin.users.edit', $this->newUser->id));
    }
}