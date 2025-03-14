<?php

namespace App\Notifications\Admin\Driver;

use App\Models\User;
use App\Models\Carrier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewDriverCreatedNotification extends Notification
{
    use Queueable;

    protected $user;
    protected $carrier;
    protected $password;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, Carrier $carrier, string $password)
    {
        $this->user = $user;
        $this->carrier = $carrier;
        $this->password = $password;
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
            ->subject('New Driver Account Created')
            ->line('Welcome to ' . $this->carrier->name)
            ->line('Your account has been created with the following credentials:')
            ->line('Email: ' . $this->user->email)
            ->line('Password: ' . $this->password)
            ->line('Please complete your application process by clicking the button below:')
            ->action('Complete Application', route('admin.carrier.user_drivers.application.step1', $this->carrier))
            ->line('Thank you for using our application!');
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
