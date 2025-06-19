<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmploymentVerification extends Mailable
{
    use Queueable, SerializesModels;

    public $companyName;
    public $driverName;
    public $employmentData;
    public $token;
    public $driverId;
    public $employmentId;

    /**
     * Create a new message instance.
     *
     * @param string $companyName
     * @param string $driverName
     * @param array $employmentData
     * @param string $token
     * @param int $driverId
     * @param int $employmentId
     * @return void
     */
    public function __construct(
        $companyName,
        $driverName,
        $employmentData,
        $token,
        $driverId,
        $employmentId
    ) {
        $this->companyName = $companyName;
        $this->driverName = $driverName;
        $this->employmentData = $employmentData;
        $this->token = $token;
        $this->driverId = $driverId;
        $this->employmentId = $employmentId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Employment Verification Request for ' . $this->driverName)
                    ->markdown('emails.employment-verification');
    }
}
