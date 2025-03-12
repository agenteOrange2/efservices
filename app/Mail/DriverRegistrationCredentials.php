<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DriverRegistrationCredentials extends Mailable implements ShouldQueue
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
        return $this->subject('Your Driver Registration Credentials')
                    ->markdown('emails.driver.registration-credentials');
    }
}