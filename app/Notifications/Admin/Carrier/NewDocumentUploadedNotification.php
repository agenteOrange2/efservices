<?php

namespace App\Notifications\Admin\Carrier;

use Illuminate\Bus\Queueable;
use App\Models\CarrierDocument;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewDocumentUploadedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $document;

    /**
     * Create a new notification instance.
     */
    public function __construct(CarrierDocument $document)
    {
        $this->document = $document;
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
            ->subject('Nuevo Documento Subido')
            ->line('Se ha subido un nuevo documento para el carrier ' . $this->document->carrier->name)
            ->line('Detalles del documento:')
            ->line('Tipo: ' . $this->document->documentType->name)
            ->line('Fecha: ' . $this->document->date)
            ->action('Ver Documento', route('admin.carrier_documents.show', $this->document->id));
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
