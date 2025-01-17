<?php

namespace App\Notifications\Admin\Carrier;

use App\Models\User;
use App\Models\Carrier;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewUserCarrierNotification extends Notification implements ShouldQueue
{
    use Queueable;


    protected $user;
    protected $carrier;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, Carrier $carrier)
    {
        $this->user = $user;
        $this->carrier = $carrier;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['mail'];
    }


    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Nuevo Usuario Asociado al Carrier')
            ->line('Se ha creado un nuevo usuario asociado al carrier ' . $this->carrier->name)
            ->line('Detalles del usuario:')
            ->line('Nombre: ' . $this->user->name)
            ->line('Email: ' . $this->user->email)
            ->action('Ver Usuario', route('admin.carriers.user_carriers.edit', [$this->carrier->id, $this->user->id]));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
