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
            ->subject('New Document Uploaded')
            ->line('A new document has been uploaded for carrier ' . $this->document->carrier->name)
            ->line('Document details:')
            ->line('Type: ' . $this->document->documentType->name)
            ->line('Date: ' . $this->document->date)
            ->action('View Document', route('admin.carrier_documents.show', $this->document->id));
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
