<?php

namespace App\Notifications\Admin\Carrier;

use App\Models\Carrier;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewCarrierNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $carrier;
    
    public function __construct(Carrier $carrier)
    {
        $this->carrier = $carrier;
        Log::info('NewCarrierNotification constructor called', [
            'carrier_id' => $carrier->id,
            'carrier_name' => $carrier->name
        ]);
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        try {
            $url = route('admin.carrier.user_carriers.index', $this->carrier);
            
            Log::info('NewCarrierNotification toMail method called', [
                'admin_email' => config('app.admin_email'),
                'carrier_details' => [
                    'name' => $this->carrier->name,
                    'address' => $this->carrier->address,
                    'state' => $this->carrier->state
                ],
                'url' => $url
            ]);

            return (new MailMessage)
                ->subject('Nuevo Carrier Registrado en el Sistema')
                ->greeting('¡Hola Administrador!')
                ->line('Se ha registrado un nuevo carrier en el sistema.')
                ->line('Detalles del carrier:')
                ->line('Nombre: ' . $this->carrier->name)
                ->line('Dirección: ' . $this->carrier->address)
                ->line('Estado: ' . $this->carrier->state)
                ->line('DOT Number: ' . $this->carrier->dot_number)
                ->line('Fecha de creación: ' . $this->carrier->created_at->format('d/m/Y H:i'))
                ->action('Ver Carrier', $url);
        } catch (\Exception $e) {
            Log::error('Error generando el email de notificación', [
                'error' => $e->getMessage(),
                'carrier_id' => $this->carrier->id
            ]);
            
            // Retornar email sin el botón de acción si hay error con la URL
            return (new MailMessage)
                ->subject('Nuevo Carrier Registrado en el Sistema')
                ->greeting('¡Hola Administrador!')
                ->line('Se ha registrado un nuevo carrier en el sistema.')
                ->line('Detalles del carrier:')
                ->line('Nombre: ' . $this->carrier->name)
                ->line('Dirección: ' . $this->carrier->address)
                ->line('Estado: ' . $this->carrier->state)
                ->line('DOT Number: ' . $this->carrier->dot_number)
                ->line('Fecha de creación: ' . $this->carrier->created_at->format('d/m/Y H:i'));
        }
    }

    public function toArray(object $notifiable): array
    {
        return [
            'carrier_id' => $this->carrier->id,
            'carrier_name' => $this->carrier->name,
        ];
    }
}