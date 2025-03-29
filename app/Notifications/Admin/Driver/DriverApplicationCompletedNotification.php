<?php
namespace App\Notifications\Admin\Driver;

use App\Models\User;
use App\Models\Carrier;
use App\Models\UserDriverDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DriverApplicationCompletedNotification extends Notification
{
    use Queueable;

    protected $driver;
    protected $carrier;
    protected $driverDetail;

    public function __construct(User $driver, Carrier $carrier, UserDriverDetail $driverDetail)
    {
        $this->driver = $driver;
        $this->carrier = $carrier;
        $this->driverDetail = $driverDetail;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Driver Application Completed')
            ->greeting('Hello Admin!')
            ->line('A driver has completed their application and is pending review.')
            ->line('Driver Name: ' . $this->driver->name)
            ->line('Carrier: ' . $this->carrier->name)
            ->action('Review Application', url('/admin/carriers/' . $this->carrier->slug . '/drivers/' . $this->driverDetail->id . '/edit'))
            ->line('Please review the application as soon as possible.');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'driver_id' => $this->driver->id,
            'driver_detail_id' => $this->driverDetail->id,
            'title' => 'Driver application completed',
            'message' => "Driver {$this->driver->name} from {$this->carrier->name} has completed their application",
            'icon' => 'CircleCheckBig', // Asegúrate de que este icono exista en tu UI
            'action_url' => '/admin/carriers/' . $this->carrier->slug . '/drivers/' . $this->driverDetail->id . '/edit'
        ];
    }
}