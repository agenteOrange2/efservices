<?php

namespace App\Mail;

use App\Models\Admin\Driver\DriverApplication;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DriverApplicationRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $driverApplication;
    public $user;
    public $rejectionReason;

    /**
     * Create a new message instance.
     */
    public function __construct(DriverApplication $driverApplication, User $user, string $rejectionReason)
    {
        $this->driverApplication = $driverApplication;
        $this->user = $user;
        $this->rejectionReason = $rejectionReason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Driver Application Rejected - Action Required',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.driver.application-rejected',
            with: [
                'driverApplication' => $this->driverApplication,
                'user' => $this->user,
                'rejectionReason' => $this->rejectionReason,
                'dashboardUrl' => route('driver.dashboard'),
                'applicationUrl' => route('driver.application.edit')
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