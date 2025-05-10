<?php

namespace App\Notifications\Admin\Driver;

use App\Models\User;  
use App\Models\Carrier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewDriverNotificationAdmin extends Notification
{
    use Queueable;

    protected $user;
    protected $carrier;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, Carrier $carrier) // Usar App\Models\User
    {
        $this->user = $user;
        $this->carrier = $carrier;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Driver Registration')
            ->line('A new driver has been registered.')
            ->line('Driver: ' . $this->user->name)
            ->line('Carrier: ' . $this->carrier->name)
            ->action('View Driver', route('admin.carrier.user_drivers.edit', [$this->carrier, $this->user->driverDetails]))
            ->line('The driver will now complete their application process.');
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
