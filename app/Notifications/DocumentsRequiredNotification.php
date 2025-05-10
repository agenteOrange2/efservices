<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\UserDriverDetail;

class DocumentsRequiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $driver;
    protected $application;
    protected $requestedDocuments;
    protected $additionalRequirements;
    protected $documentReasons;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(UserDriverDetail $driver, DriverApplication $application, array $requestedDocuments, string $additionalRequirements = null, array $documentReasons = [])
    {
        $this->driver = $driver;
        $this->application = $application;
        $this->requestedDocuments = $requestedDocuments;
        $this->additionalRequirements = $additionalRequirements;
        $this->documentReasons = $documentReasons;
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
        $isCarrier = $notifiable->id !== $this->driver->user_id;
        
        $mailMessage = (new MailMessage)
            ->subject('Documentos Adicionales Requeridos - EF Services');
            
        if ($isCarrier) {
            // Mensaje para el carrier
            $mailMessage->greeting('Hola ' . $notifiable->name)
                ->line('Se requieren documentos adicionales para completar el proceso de aplicación del conductor ' . $this->driver->user->name . ' ' . $this->driver->last_name . '.')
                ->line('Por favor asegúrese de que el conductor proporcione los siguientes documentos:');
        } else {
            // Mensaje para el driver
            $mailMessage->greeting('Hola ' . $this->driver->user->name . ' ' . $this->driver->last_name)
                ->line('Se requieren documentos adicionales para completar tu proceso de aplicación como conductor.')
                ->line('Por favor proporciona los siguientes documentos:');
        }

        // Mapeo de documentos solicitados a nombres legibles
        $documentNames = [
            'ssn_card' => 'Tarjeta de Seguro Social',
            'license' => 'Licencia de Conducir',
            'medical_card' => 'Tarjeta Médica Actualizada',
            'proof_address' => 'Comprobante de Domicilio',
            'employment_verification' => 'Verificación de Empleo Anterior'
        ];

        // Agregar cada documento solicitado con su razón
        foreach ($this->requestedDocuments as $docKey) {
            $docName = $documentNames[$docKey] ?? $docKey;
            $reason = isset($this->documentReasons[$docKey]) ? " - Motivo: " . $this->documentReasons[$docKey] : "";
            $mailMessage->line("• {$docName}{$reason}");
        }

        // Agregar requisitos adicionales si existen
        if (!empty($this->additionalRequirements)) {
            $mailMessage->line('Requisitos adicionales:')
                ->line($this->additionalRequirements);
        }

        return $mailMessage
            ->action('Ver Detalles', url('/driver/application'))
            ->line('Gracias por tu colaboración.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'driver_id' => $this->driver->id,
            'application_id' => $this->application->id,
            'requested_documents' => $this->requestedDocuments,
            'additional_requirements' => $this->additionalRequirements,
            'document_reasons' => $this->documentReasons,
            'title' => 'Documentos Adicionales Requeridos',
            'message' => 'Se requieren documentos adicionales para completar tu proceso de aplicación.',
            'type' => 'document_request'
        ];
    }
}
