<?php

namespace App\Mail;

use App\Models\Carrier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CarrierReactivatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $carrier;

    /**
     * Create a new message instance.
     */
    public function __construct(Carrier $carrier)
    {
        $this->carrier = $carrier;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Account Reactivated - Welcome Back to EF Services!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.carrier.reactivated',
            with: [
                'carrier' => $this->carrier,
                'dashboardUrl' => route('carrier.dashboard'),
                'supportUrl' => route('carrier.support')
            ]
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