<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class ThirdPartyVehicleVerification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $thirdPartyName,
        public string $driverName,
        public array $vehicleData,
        public string $verificationToken,
        public int $driverId,
        public int $applicationId
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'EF Services - Vehicle Verification Required',
            from: new Address('noreply@efservices.com', 'EF Services')
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        try {
            return new Content(
                view: 'emails.third-party-vehicle-verification',
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error en ThirdPartyVehicleVerification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'third_party_name' => $this->thirdPartyName,
                'driver_name' => $this->driverName,
                'vehicle_data' => $this->vehicleData,
            ]);
            throw $e;
        }
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
