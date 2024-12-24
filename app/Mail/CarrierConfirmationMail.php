<?php

namespace App\Mail;

use App\Models\UserCarrier;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class CarrierConfirmationMail extends Mailable
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
            subject: 'Confirm Your Email',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.carrier_confirmation',
            with: [
                'url' => route('user_carrier.confirm', ['token' => $this->userCarrier->confirmation_token]), // Cambiar el alias
                'userCarrier' => $this->userCarrier,
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
