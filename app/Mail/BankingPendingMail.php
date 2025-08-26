<?php

namespace App\Mail;

use App\Models\Carrier;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BankingPendingMail extends Mailable
{
    use Queueable, SerializesModels;

    public $carrier;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct(Carrier $carrier, User $user = null)
    {
        $this->carrier = $carrier;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Banking Information Under Review - ' . $this->carrier->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.carrier.banking-pending',
            with: [
                'carrier' => $this->carrier,
                'user' => $this->user,
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