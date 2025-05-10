<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NewDriverNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $driverName;
    public $driverEmail;
    public $carrierId;
    public $carrierName;
    public $driverLink;

    /**
     * Create a new message instance.
     */
    public function __construct($driverName, $driverEmail, $carrierId, $carrierName)
    {
        $this->driverName = $driverName;
        $this->driverEmail = $driverEmail;
        $this->carrierId = $carrierId;
        $this->carrierName = $carrierName;
        $this->driverLink = route('admin.drivers.index');
    }

    /**
     * Build the message.
     */
    public function build()
    {
        try {
            Log::info('Construyendo correo de notificación de nuevo conductor', [
                'driver_email' => $this->driverEmail,
                'carrier_id' => $this->carrierId
            ]);
            
            return $this->subject('New Driver Registration: ' . $this->driverName)
                        ->markdown('emails.admin.new-driver-notification');
        } catch (\Exception $e) {
            Log::error('Error al construir correo de notificación de nuevo conductor', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
