<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TestAssignDriverType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:assign-driver-type {vehicle_id=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the assignDriverType method to debug license data loading';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $vehicleId = $this->argument('vehicle_id');
        $vehicle = Vehicle::find($vehicleId);
        
        if (!$vehicle) {
            $this->error("Vehicle with ID {$vehicleId} not found.");
            return 1;
        }
        
        $this->info("Testing assignDriverType for Vehicle ID: {$vehicleId}");
        $this->info("Vehicle user_driver_detail_id: {$vehicle->user_driver_detail_id}");
        
        // Simulate the assignDriverType method logic
        $driverData = null;
        
        if ($vehicle->user_driver_detail_id) {
            $driver = UserDriverDetail::with(['user', 'licenses'])
                ->find($vehicle->user_driver_detail_id);
            
            $this->info("Driver found: " . ($driver ? 'Yes' : 'No'));
            
            if ($driver) {
                $this->info("Driver ID: {$driver->id}");
                $this->info("User exists: " . ($driver->user ? 'Yes' : 'No'));
                $this->info("Licenses count: " . $driver->licenses->count());
                
                if ($driver->user) {
                    $primaryLicense = $driver->licenses()->first();
                    
                    $this->info("Primary license exists: " . ($primaryLicense ? 'Yes' : 'No'));
                    
                    if ($primaryLicense) {
                        $this->info("License details:");
                        $this->info("  - Number: {$primaryLicense->license_number}");
                        $this->info("  - Class: {$primaryLicense->license_class}");
                        $this->info("  - State: {$primaryLicense->state_of_issue}");
                        $this->info("  - Expiration: {$primaryLicense->expiration_date}");
                        
                        // Test date formatting
                        $licenseExpiration = '';
                        if ($primaryLicense->expiration_date) {
                            try {
                                $licenseExpiration = Carbon::parse($primaryLicense->expiration_date)->format('Y-m-d');
                                $this->info("  - Formatted expiration: {$licenseExpiration}");
                            } catch (\Exception $e) {
                                $this->error("Error formatting date: " . $e->getMessage());
                                $licenseExpiration = $primaryLicense->expiration_date;
                            }
                        }
                        
                        // Build driver data array
                        $fullName = trim($driver->user->name ?? '');
                        if ($driver->middle_name) {
                            $fullName .= ' ' . trim($driver->middle_name);
                        }
                        if ($driver->last_name) {
                            $fullName .= ' ' . trim($driver->last_name);
                        }
                        
                        $nameParts = explode(' ', $fullName, 2);
                        $firstName = $nameParts[0] ?? '';
                        $lastName = $nameParts[1] ?? '';
                        
                        $driverData = [
                            'first_name' => $firstName,
                            'last_name' => $lastName,
                            'phone' => $driver->phone ?? '',
                            'email' => $driver->user->email ?? '',
                            'license_number' => $primaryLicense->license_number ?? '',
                            'license_class' => $primaryLicense->license_class ?? '',
                            'license_state' => $primaryLicense->state_of_issue ?? '',
                            'license_expiration' => $licenseExpiration
                        ];
                        
                        $this->info("\nFinal driver data array:");
                        foreach ($driverData as $key => $value) {
                            $this->info("  {$key}: '{$value}'");
                        }
                    } else {
                        $this->warn("No primary license found for this driver.");
                    }
                } else {
                    $this->warn("Driver exists but no associated user found.");
                }
            } else {
                $this->warn("No driver found with the specified user_driver_detail_id.");
            }
        } else {
            $this->warn("Vehicle has no user_driver_detail_id assigned.");
        }
        
        return 0;
    }
}
