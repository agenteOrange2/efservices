<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\UserDriverDetail;

class CheckVehiclesWithDrivers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:vehicles-with-drivers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check vehicles with assigned drivers and their license data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking vehicles with assigned drivers...');
        
        $vehicles = Vehicle::whereNotNull('user_driver_detail_id')
            ->with(['driver.user', 'driver.licenses'])
            ->get();
            
        if ($vehicles->isEmpty()) {
            $this->warn('No vehicles found with assigned drivers.');
            return;
        }
        
        $this->info("Found {$vehicles->count()} vehicles with assigned drivers:");
        
        foreach ($vehicles as $vehicle) {
            $this->line("\n--- Vehicle ID: {$vehicle->id} ---");
            $this->line("Make/Model: {$vehicle->make} {$vehicle->model}");
            $this->line("VIN: {$vehicle->vin}");
            $this->line("Driver Detail ID: {$vehicle->user_driver_detail_id}");
            
            if ($vehicle->driver) {
                $driver = $vehicle->driver;
                $driverName = $driver->user->name ?? 'N/A';
                $driverPhone = $driver->phone ?? 'N/A';
                $driverEmail = $driver->user->email ?? 'N/A';
                $this->line("Driver Name: {$driverName}");
                $this->line("Driver Phone: {$driverPhone}");
                $this->line("Driver Email: {$driverEmail}");
                $this->line("Licenses Count: {$driver->licenses->count()}");
                
                if ($driver->licenses->isNotEmpty()) {
                    $primaryLicense = $driver->licenses->first();
                    $licenseNumber = $primaryLicense->license_number ?? 'N/A';
                    $licenseState = $primaryLicense->state_of_issue ?? 'N/A';
                    $licenseExpiration = $primaryLicense->expiration_date ?? 'N/A';
                    $this->line("License Number: {$licenseNumber}");
                    $this->line("License State: {$licenseState}");
                    $this->line("License Expiration: {$licenseExpiration}");
                } else {
                    $this->warn("No licenses found for this driver.");
                }
            } else {
                $this->error("Driver not found for user_driver_detail_id: {$vehicle->user_driver_detail_id}");
            }
        }
        
        $this->info("\nCheck completed.");
    }
}
