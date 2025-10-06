<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverLicense;

class CheckDriverLicense extends Command
{
    protected $signature = 'check:driver-license {driver_id}';
    protected $description = 'Check driver license data for debugging';

    public function handle()
    {
        $driverId = $this->argument('driver_id');
        
        $this->info("Checking driver ID: {$driverId}");
        
        $driver = UserDriverDetail::with(['user', 'licenses'])->find($driverId);
        
        if (!$driver) {
            $this->error("Driver not found with ID: {$driverId}");
            return 1;
        }
        
        $this->info("Driver found: {$driver->id}");
        $this->info("User: " . ($driver->user ? $driver->user->name : 'No user'));
        $this->info("Phone: " . ($driver->phone ?? 'No phone'));
        $this->info("Email: " . ($driver->user ? $driver->user->email : 'No email'));
        $this->info("Licenses count: " . $driver->licenses->count());
        
        if ($driver->licenses->count() > 0) {
            foreach ($driver->licenses as $license) {
                $this->info("License ID: {$license->id}");
                $this->info("License Number: " . ($license->license_number ?? 'N/A'));
                $this->info("License Class: " . ($license->license_class ?? 'N/A'));
                $this->info("State of Issue: " . ($license->state_of_issue ?? 'N/A'));
                $this->info("Expiration Date: " . ($license->expiration_date ?? 'N/A'));
                $this->info("---");
            }
        } else {
            $this->warn("No licenses found for this driver.");
            
            // Check if there are any licenses in the database for this driver
            $allLicenses = DriverLicense::where('user_driver_detail_id', $driverId)->get();
            $this->info("Direct query licenses count: " . $allLicenses->count());
            
            if ($allLicenses->count() > 0) {
                $this->info("Found licenses with direct query:");
                foreach ($allLicenses as $license) {
                    $this->info("License ID: {$license->id}");
                    $this->info("License Number: " . ($license->license_number ?? 'N/A'));
                    $this->info("License Class: " . ($license->license_class ?? 'N/A'));
                    $this->info("State of Issue: " . ($license->state_of_issue ?? 'N/A'));
                    $this->info("Expiration Date: " . ($license->expiration_date ?? 'N/A'));
                    $this->info("---");
                }
            }
        }
        
        return 0;
    }
}