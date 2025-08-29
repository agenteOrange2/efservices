<?php

namespace App\Mail;

use App\Models\UserCarrier;
use App\Models\User;
use App\Models\Carrier;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewCarrierAdminNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $carrier;
    public $eventType;
    public $step;
    public $data;
    public $userCarrier; // Mantener compatibilidad hacia atrás

    /**
     * Create a new message instance.
     */
    public function __construct(
        $userOrUserCarrier,
        ?Carrier $carrier = null,
        ?string $eventType = null,
        ?string $step = null,
        array $data = []
    ) {
        // Compatibilidad hacia atrás con UserCarrier
        if ($userOrUserCarrier instanceof UserCarrier) {
            $this->userCarrier = $userOrUserCarrier;
            $this->user = $userOrUserCarrier->user;
            $this->carrier = $userOrUserCarrier->carrier;
            $this->eventType = 'legacy';
            $this->step = null;
            $this->data = [];
        } else {
            // Nuevo sistema con User
            $this->user = $userOrUserCarrier;
            $this->carrier = $carrier;
            $this->eventType = $eventType ?? 'unknown';
            $this->step = $step;
            $this->data = $data;
            $this->userCarrier = null;
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->getSubjectByEventType();
        
        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get subject based on event type
     */
    private function getSubjectByEventType(): string
    {
        return match($this->eventType) {
            'step_completed' => "Carrier Step Completed: {$this->step}",
            'registration_completed' => 'New Carrier Registration Completed',
            'legacy' => 'New Carrier Admin Notification',
            default => 'Carrier Notification'
        };
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.new_carrier_admin_notification',
            with: [
                'user' => $this->user,
                'carrier' => $this->carrier,
                'eventType' => $this->eventType,
                'step' => $this->step,
                'data' => $this->data,
                'userCarrier' => $this->userCarrier, // Compatibilidad hacia atrás
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
