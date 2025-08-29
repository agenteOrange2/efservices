<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Carrier;

class AdminNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $carrier;
    public $eventType;
    public $step;
    public $title;
    public $message;
    public $data;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, ?Carrier $carrier, string $eventType, ?string $step, string $title, string $message, array $data = [])
    {
        $this->user = $user;
        $this->carrier = $carrier;
        $this->eventType = $eventType;
        $this->step = $step;
        $this->title = $title;
        $this->message = $message;
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[EFCTS] ' . $this->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-notification',
            with: [
                'user' => $this->user,
                'carrier' => $this->carrier,
                'eventType' => $this->eventType,
                'step' => $this->step,
                'title' => $this->title,
                'message' => $this->message,
                'data' => $this->data,
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