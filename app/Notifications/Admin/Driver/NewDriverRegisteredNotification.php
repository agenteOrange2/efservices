<?php
namespace App\Notifications\Admin\Driver;

use App\Models\User;
use App\Models\Carrier;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewDriverRegisteredNotification extends Notification
{
    use Queueable;

    protected $driver;
    protected $carrier;

    public function __construct(User $driver, Carrier $carrier)
    {
        $this->driver = $driver;
        $this->carrier = $carrier;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Driver Registration')
            ->greeting('Hello Admin!')
            ->line('A new driver has registered on the platform.')
            ->line('Driver Name: ' . $this->driver->name)
            ->line('Carrier: ' . $this->carrier->name)
            ->action('Review Driver', url('/admin/drivers/' . $this->driver->id))
            ->line('Please review their application.');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'driver_id' => $this->driver->id,
            'title' => 'New driver registered',
            'message' => "New driver registered for {$this->carrier->name}: {$this->driver->name}",            
            'icon' => 'UserPlus', // Asegúrate de que este icono exista en tu UI
            'action_url' => '/admin/carriers/' . $this->carrier->slug . '/drivers/' . $this->driver->driverDetails->id . '/edit'
        ];
    }

    public function toArray($notifiable): array
    {
        return [
            'driver_id' => $this->driver->id,
            'driver_name' => $this->driver->name,
            'carrier_id' => $this->carrier->id,
            'carrier_name' => $this->carrier->name,
            'message' => 'New driver registration'
        ];
    }
}