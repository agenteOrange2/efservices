<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ThirdPartyVehicleVerification;
use App\Models\VehicleVerificationToken;
use App\Models\ApplicationStep;
use App\Models\Vehicle;
use App\Models\ThirdParty;

class TestThirdPartyMail extends Command
{
    protected $signature = 'mail:test-third-party';
    protected $description = 'Test third party email sending using ThirdPartyVehicleVerification class';

    public function handle()
    {
        $this->info('Testing third party email sending...');
        
        try {
            // Log SMTP configuration
            $this->info('SMTP Configuration:');
            $this->info('Host: ' . config('mail.mailers.smtp.host'));
            $this->info('Port: ' . config('mail.mailers.smtp.port'));
            $this->info('Encryption: ' . config('mail.mailers.smtp.encryption'));
            $this->info('Username: ' . config('mail.mailers.smtp.username'));
            $this->info('From Address: ' . config('mail.from.address'));
            $this->info('From Name: ' . config('mail.from.name'));
            
            Log::info('TestThirdPartyMail: Starting email test', [
                'smtp_host' => config('mail.mailers.smtp.host'),
                'smtp_port' => config('mail.mailers.smtp.port'),
                'smtp_encryption' => config('mail.mailers.smtp.encryption'),
                'smtp_username' => config('mail.mailers.smtp.username'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name')
            ]);

            // Create test data similar to what sendThirdPartyEmail uses
            $testToken = 'test-token-' . time();
            $testEmail = 'frontend@kuiraweb.com';
            
            // Create test data matching the exact parameters used in sendThirdPartyEmail
            $thirdPartyName = 'Test Third Party Company';
            $driverName = 'John Doe';
            $vehicleData = [
                'make' => 'Toyota',
                'model' => 'Camry',
                'year' => '2020',
                'vin' => 'TEST123456789VIN',
                'type' => 'Truck',
                'registration_number' => 'TEST123',
                'registration_state' => 'FL'
            ];
            $driverId = 999;
            $applicationId = 888;
            
            $this->info('Creating ThirdPartyVehicleVerification email...');
            
            // Create the email using the same class and parameters as sendThirdPartyEmail
            $email = new ThirdPartyVehicleVerification(
                $thirdPartyName,
                $driverName,
                $vehicleData,
                $testToken,
                $driverId,
                $applicationId
            );
            
            $this->info('Sending email to: ' . $testEmail);
            
            // Send the email
            Mail::to($testEmail)->send($email);
            
            $this->info('Email sent successfully!');
            
            Log::info('TestThirdPartyMail: Email sent successfully', [
                'recipient' => $testEmail,
                'token' => $testToken,
                'third_party_name' => $thirdPartyName,
                'driver_name' => $driverName,
                'vehicle_data' => $vehicleData,
                'driver_id' => $driverId,
                'application_id' => $applicationId
            ]);
            
        } catch (\Swift_TransportException $e) {
            $this->error('Swift Transport Exception: ' . $e->getMessage());
            Log::error('TestThirdPartyMail: Swift Transport Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        } catch (\Symfony\Component\Mailer\Exception\TransportException $e) {
            $this->error('Symfony Transport Exception: ' . $e->getMessage());
            Log::error('TestThirdPartyMail: Symfony Transport Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        } catch (\Exception $e) {
            $this->error('General Exception: ' . $e->getMessage());
            Log::error('TestThirdPartyMail: General Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        $this->info('Test completed. Check logs for detailed information.');
    }
}