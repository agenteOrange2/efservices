<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class DriverRegistrationCredentials extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $password;
    public $resumeLink;

    /**
     * Create a new message instance.
     */
    public function __construct($name, $email, $password, $resumeLink)
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->resumeLink = $resumeLink;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        try {
            Log::info('Construyendo correo de credenciales', [
                'email' => $this->email,
                'template' => 'emails.driver.registration-credential'
            ]);
            
            return $this->subject('Your Driver Registration Credentials')
                        ->markdown('emails.driver.registration-credential');
        } catch (\Exception $e) {
            Log::error('Error al construir correo de credenciales', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}