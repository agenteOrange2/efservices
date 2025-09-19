<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserDriverDetail;
use App\Models\Admin\Vehicle\Vehicle;
use Illuminate\Support\Facades\Log;

class TestVehicleCreation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:vehicle-creation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test vehicle creation functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('Testing vehicle creation...');
            
            // Get first user driver detail
            $userDriverDetail = UserDriverDetail::first();
            
            if (!$userDriverDetail) {
                $this->error('No UserDriverDetail found');
                return 1;
            }
            
            $this->info("Found UserDriverDetail ID: {$userDriverDetail->id}");
            $this->info("Carrier ID: {$userDriverDetail->carrier_id}");
            
            // Test vehicle creation
            $vehicleData = [
                'make' => 'Test Make',
                'model' => 'Test Model', 
                'year' => 2020,
                'vin' => '1HGBH41JXMN109188', // Different VIN to avoid conflicts
                'type' => 'truck', // Campo requerido
                'registration_state' => 'TX', // Campo requerido
                'registration_number' => 'TEST123', // Campo requerido
                'registration_expiration_date' => '2025-12-31', // Campo requerido
                'fuel_type' => 'diesel', // Campo requerido
                'carrier_id' => $userDriverDetail->carrier_id,
                'user_driver_detail_id' => $userDriverDetail->id
            ];
            
            $this->info('Creating vehicle with data: ' . json_encode($vehicleData));
            
            $vehicle = Vehicle::create($vehicleData);
            
            $this->info("Vehicle created successfully with ID: {$vehicle->id}");
            $this->info("Vehicle VIN: {$vehicle->vin}");
            
            // Verify vehicle exists
            $foundVehicle = Vehicle::find($vehicle->id);
            if ($foundVehicle) {
                $this->info('Vehicle verification: SUCCESS');
            } else {
                $this->error('Vehicle verification: FAILED');
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error('Trace: ' . $e->getTraceAsString());
            return 1;
        }
    }
}
