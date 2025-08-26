<?php

namespace App\Mail;

use App\Models\Carrier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BankingRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $carrier;
    public $rejectionReason;

    /**
     * Create a new message instance.
     */
    public function __construct(Carrier $carrier, string $rejectionReason)
    {
        $this->carrier = $carrier;
        $this->rejectionReason = $rejectionReason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Banking Information Rejected - Action Required',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.carrier.banking-rejected',
            with: [
                'carrier' => $this->carrier,
                'rejectionReason' => $this->rejectionReason,
                'dashboardUrl' => route('carrier.dashboard'),
                'bankingUrl' => route('carrier.dashboard')
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