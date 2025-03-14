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
        return ['mail'];
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
            
            Log::info('URL generada correctamente', ['url' => $url]);
    
            $message = (new MailMessage)
                ->subject('Nuevo Usuario Carrier Creado')
                ->greeting('¡Hola!')
                ->line('Se ha creado un nuevo usuario carrier en el sistema.')
                ->line('Detalles del usuario:')
                ->line('Nombre: ' . $this->user->name)
                ->line('Email: ' . $this->user->email)
                ->line('Carrier: ' . $this->carrier->name)
                ->line('Fecha de creación: ' . $this->user->created_at->format('d/m/Y H:i'))
                ->action('Ver Usuario', $url)
                ->line('Gracias por usar nuestra aplicación.');
    
            Log::info('Mail message creado correctamente');
            
            return $message;
        } catch (\Exception $e) {
            Log::error('Error en toMail', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
