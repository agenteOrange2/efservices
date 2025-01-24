<?php
namespace App\Notifications\Driver;

use App\Models\User;
use App\Models\Carrier;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewDriverRegistrationNotification extends Notification
{
    use Queueable;

    protected $user;
    protected $carrier;

    public function __construct(User $user, Carrier $carrier)
    {
        $this->user = $user;
        $this->carrier = $carrier;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $isSuperAdmin = $notifiable->hasRole('superadmin');
        
        if ($isSuperAdmin) {
            return (new MailMessage)
                ->subject('New Driver Registration')
                ->line("A new driver has registered:")
                ->line("Name: {$this->user->name}")
                ->line("Carrier: {$this->carrier->name}")
                ->action('Review Driver', url("/admin/drivers/{$this->user->id}"));
        }

        return (new MailMessage)
            ->subject('Welcome to ' . $this->carrier->name)
            ->line("Welcome {$this->user->name}!")
            ->line("You've been registered as a driver for {$this->carrier->name}")
            ->line('Please wait for account activation.')
            ->action('Visit Dashboard', url('/driver/dashboard'));
    }

    public function toArray($notifiable): array
    {
        return [
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'carrier_id' => $this->carrier->id,
            'carrier_name' => $this->carrier->name,
            'message' => $notifiable->hasRole('superadmin') 
                ? 'New driver registration'
                : 'Welcome to ' . $this->carrier->name
        ];
    }
}