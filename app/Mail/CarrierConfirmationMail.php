<?php

namespace App\Mail;

use App\Models\UserCarrierDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class CarrierConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $userCarrierDetail;

    /**
     * Create a new message instance.
     */
    public function __construct(UserCarrierDetail $userCarrierDetail)
    {
        $this->userCarrierDetail = $userCarrierDetail;
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
                'url' => route('carrier.confirm', ['token' => $this->userCarrierDetail->confirmation_token]),
                'userCarrier' => $this->userCarrierDetail->user, // Si necesitas acceso al usuario principal
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
