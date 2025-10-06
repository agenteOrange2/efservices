<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Admin\Driver\DriverLicense;
use Carbon\Carbon;

class CreateDriverLicense extends Command
{
    protected $signature = 'create:driver-license {driver_id}';
    protected $description = 'Create a test license for a driver';

    public function handle()
    {
        $driverId = $this->argument('driver_id');
        
        $this->info("Creating license for driver ID: {$driverId}");
        
        $license = DriverLicense::create([
            'user_driver_detail_id' => $driverId,
            'current_license_number' => 'DL123456789',
            'license_number' => 'DL123456789',
            'license_class' => 'CDL-A',
            'state_of_issue' => 'TX',
            'expiration_date' => Carbon::now()->addYears(2)->format('Y-m-d'),
            'is_cdl' => true,
            'is_primary' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        $this->info("License created successfully!");
        $this->info("License ID: {$license->id}");
        $this->info("License Number: {$license->license_number}");
        $this->info("License Class: {$license->license_class}");
        $this->info("State of Issue: {$license->state_of_issue}");
        $this->info("Expiration Date: {$license->expiration_date}");
        
        return 0;
    }
}