<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DriverContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public $contactData;
    public $senderName;
    public $senderEmail;

    /**
     * Create a new message instance.
     */
    public function __construct($contactData, $senderName, $senderEmail)
    {
        $this->contactData = $contactData;
        $this->senderName = $senderName;
        $this->senderEmail = $senderEmail;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Contact from EF Services Admin')
                    ->view('emails.driver-contact');
    }
}