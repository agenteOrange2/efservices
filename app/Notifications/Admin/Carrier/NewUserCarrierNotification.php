<?php

namespace App\Notifications\Admin\Carrier;

use App\Models\User;
use App\Models\Carrier;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
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
        Log::info('NewUserCarrierNotification constructor called', [
            'user_id' => $user->id,
            'carrier_id' => $carrier->id
        ]);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        // Añadimos 'database' para las notificaciones en la campana
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        try {
            Log::info('NewUserCarrierNotification toMail method called', [
                'carrier_name' => $this->carrier->name,
                'user_details' => [
                    'name' => $this->user->name,
                    'email' => $this->user->email
                ]
            ]);
    
            $url = route('admin.carrier.user_carriers.index', ['carrier' => $this->carrier->slug]);
            
            Log::info('URL generated correctly', ['url' => $url]);
    
            $message = (new MailMessage)
                ->subject('New Carrier User Created')
                ->greeting('Hello!')
                ->line('A new carrier user has been created in the system.')
                ->line('User details:')
                ->line('Name: ' . $this->user->name)
                ->line('Email: ' . $this->user->email)
                ->line('Carrier: ' . $this->carrier->name)
                ->line('Creation date: ' . $this->user->created_at->format('m/d/Y H:i'))
                ->action('View User', $url)
                ->line('Thank you for using our application.');
    
            Log::info('Mail message created correctly');
            
            return $message;
        } catch (\Exception $e) {
            Log::error('Error in toMail', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable)
    {
        return [
            'user_id' => $this->user->id,
            'carrier_id' => $this->carrier->id,
            'title' => 'New carrier user created',
            'message' => "New user registered for carrier {$this->carrier->name}: {$this->user->name}",
            'icon' => 'UserPlus', // Icono para usar en la UI
            'action_url' => '/admin/carriers/' . $this->carrier->slug . '/users'
        ];
    }
}