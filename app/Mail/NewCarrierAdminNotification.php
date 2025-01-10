<?php

namespace App\Mail;

use App\Models\UserCarrier;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewCarrierAdminNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $userCarrier;

    /**
     * Create a new message instance.
     */
    public function __construct(UserCarrier $userCarrier)
    {
        $this->userCarrier = $userCarrier;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Carrier Admin Notification',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.new_carrier_admin_notification', // Vista que mostrarás en el correo
            with: [
                'userCarrier' => $this->userCarrier, // Pasarás el modelo como datos
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
