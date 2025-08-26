<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ThirdPartyVehicleVerification;
use App\Models\VehicleVerificationToken;

class TestThirdPartyEmailCommand extends Command
{
    protected $signature = 'mail:test-third-party {email : The email address to send the test to}';
    protected $description = 'Test third party email sending';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info('Testing third party email sending...');
        $this->info('Email: ' . $email);
        $this->info('SMTP Config:');
        $this->info('- Host: ' . config('mail.mailers.smtp.host'));
        $this->info('- Port: ' . config('mail.mailers.smtp.port'));
        $this->info('- Encryption: ' . config('mail.mailers.smtp.encryption'));
        $this->info('- Username: ' . config('mail.mailers.smtp.username'));
        $this->info('- Queue Connection: ' . config('queue.default'));
        
        try {
            // Create a test token
            $token = VehicleVerificationToken::create([
                'token' => 'test-token-' . time(),
                'third_party_email' => $email,
                'driver_id' => 1,
                'application_id' => 1,
                'expires_at' => now()->addDays(7)
            ]);
            
            // Test vehicle data
            $vehicleData = [
                'make' => 'Test Make',
                'model' => 'Test Model',
                'year' => '2023',
                'vin' => 'TEST123456789'
            ];
            
            // Send email using ThirdPartyVehicleVerification class
            Mail::to($email)->send(new ThirdPartyVehicleVerification(
                'Test Company Representative',
                'Test Driver Name',
                $vehicleData,
                $token,
                1,
                1
            ));
            
            $this->info('✅ Email sent successfully!');
            $this->info('Check your email inbox (including spam folder)');
            
            // Clean up test token
            $token->delete();
            
        } catch (\Exception $e) {
            $this->error('❌ Error sending email: ' . $e->getMessage());
            Log::error('Test third party email error: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}