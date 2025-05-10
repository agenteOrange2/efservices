<?php

namespace App\Notifications\Admin\Vehicle;

use App\Models\Admin\Vehicle\VehicleMaintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $maintenance;
    protected $daysRemaining;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(VehicleMaintenance $maintenance, int $daysRemaining)
    {
        $this->maintenance = $maintenance;
        $this->daysRemaining = $daysRemaining;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $vehicle = $this->maintenance->vehicle;
        $url = route('admin.vehicles.show', $vehicle->id);
        $isDriver = $notifiable->driverDetails && $notifiable->driverDetails->id === $vehicle->driver_id;
        $isCarrier = $notifiable->carrierDetails && $notifiable->carrierDetails->carrier_id === $vehicle->carrier_id;
        
        $mailMessage = (new MailMessage)
            ->subject('Mantenimiento de Vehículo Próximo a Vencer')
            ->greeting('Hola ' . $notifiable->name);
            
        if ($isDriver) {
            // Mensaje para conductores
            $mailMessage->line('El mantenimiento de tu vehículo está próximo a vencer.')
                ->line('Es importante que programes este servicio lo antes posible para mantener tu vehículo en óptimas condiciones.');
        } elseif ($isCarrier) {
            // Mensaje para carriers
            $mailMessage->line('El mantenimiento de uno de los vehículos de tu flota está próximo a vencer.')
                ->line('Por favor, coordina con el conductor para programar este servicio lo antes posible.');
        } else {
            // Mensaje para administradores
            $mailMessage->line('El mantenimiento del siguiente vehículo está próximo a vencer.')
                ->line('Por favor, verifica que se programe este servicio lo antes posible.');
        }
        
        // Información común para todos
        $mailMessage->line('Vehículo: ' . $vehicle->make . ' ' . $vehicle->model . ' (' . $vehicle->year . ')')
            ->line('Placa: ' . $vehicle->license_plate)
            ->line('Servicio: ' . $this->maintenance->service_tasks)
            ->line('Fecha de vencimiento: ' . $this->maintenance->next_service_date->format('d/m/Y'))
            ->line('Días restantes: ' . $this->daysRemaining)
            ->action('Ver detalles', $url)
            ->line('Gracias por utilizar nuestra aplicación!');
            
        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $vehicle = $this->maintenance->vehicle;
        $isDriver = $notifiable->driverDetails && $notifiable->driverDetails->id === $vehicle->driver_id;
        $isCarrier = $notifiable->carrierDetails && $notifiable->carrierDetails->carrier_id === $vehicle->carrier_id;
        
        $title = 'Mantenimiento Próximo a Vencer';
        $message = '';
        
        if ($isDriver) {
            // Mensaje para conductores
            $message = 'El mantenimiento de tu vehículo ' . $vehicle->make . ' ' . $vehicle->model . ' (' . $vehicle->license_plate . ') vence en ' . $this->daysRemaining . ' días. Programa este servicio lo antes posible.';
        } elseif ($isCarrier) {
            // Mensaje para carriers
            $message = 'El mantenimiento del vehículo ' . $vehicle->make . ' ' . $vehicle->model . ' (' . $vehicle->license_plate . ') de tu flota vence en ' . $this->daysRemaining . ' días. Coordina con el conductor para programar este servicio.';
        } else {
            // Mensaje para administradores
            $message = 'El mantenimiento del vehículo ' . $vehicle->make . ' ' . $vehicle->model . ' (' . $vehicle->license_plate . ') vence en ' . $this->daysRemaining . ' días. Verifica que se programe este servicio.';
        }
        
        return [
            'title' => $title,
            'message' => $message,
            'icon' => 'Clock',
            'color' => 'warning',
            'link' => route('admin.vehicles.show', $vehicle->id),
            'vehicle_id' => $vehicle->id,
            'maintenance_id' => $this->maintenance->id,
            'days_remaining' => $this->daysRemaining,
            'user_type' => $isDriver ? 'driver' : ($isCarrier ? 'carrier' : 'admin')
        ];
    }
}
