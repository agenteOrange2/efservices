<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\OwnerOperatorDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestOwnerOperatorCreation extends Command
{
    protected $signature = 'test:owner-operator-creation';
    protected $description = 'Test Owner Operator creation functionality';

    public function handle()
    {
        $this->info('Testing Owner Operator creation...');
        
        try {
            DB::beginTransaction();
            
            // Buscar un UserDriverDetail existente o crear uno de prueba
            $userDriverDetail = UserDriverDetail::first();
            if (!$userDriverDetail) {
                $this->error('No UserDriverDetail found. Please create a driver first.');
                return 1;
            }
            
            $this->info('Using driver: ' . $userDriverDetail->id);
            
            // Crear o obtener aplicación
            $application = $userDriverDetail->application;
            if (!$application) {
                $application = DriverApplication::create([
                    'user_id' => $userDriverDetail->user_id,
                    'status' => 'draft'
                ]);
                $this->info('Created new application: ' . $application->id);
            } else {
                $this->info('Using existing application: ' . $application->id);
            }
            
            // Crear vehículo de prueba
            $vehicleData = [
                'make' => 'Freightliner',
                'model' => 'Cascadia',
                'year' => 2020,
                'vin' => 'TEST' . time() . 'VIN123456',
                'type' => 'tractor',
                'fuel_type' => 'diesel',
                'registration_state' => 'TX',
                'registration_number' => 'TEST' . time(),
                'registration_expiration_date' => '2025-12-31',
                'driver_type' => 'owner_operator',
                'status' => 'active',
                'carrier_id' => $userDriverDetail->carrier_id,
                'user_driver_detail_id' => $userDriverDetail->id,
            ];
            
            $vehicle = Vehicle::create($vehicleData);
            $this->info('Created vehicle: ' . $vehicle->id);
            
            // Crear Owner Operator Detail
            $ownerOperatorData = [
                'driver_application_id' => $application->id,
                'vehicle_id' => $vehicle->id,
                'owner_name' => 'Test Owner',
                'owner_phone' => '1234567890',
                'owner_email' => 'test@example.com',
                'contract_agreed' => true,
            ];
            
            $ownerDetails = $application->ownerOperatorDetail()->updateOrCreate(
                ['driver_application_id' => $application->id],
                $ownerOperatorData
            );
            
            $this->info('Created Owner Operator Detail: ' . $ownerDetails->id);
            
            // Verificar que todo se creó correctamente
            $vehicleExists = Vehicle::find($vehicle->id);
            $ownerExists = OwnerOperatorDetail::find($ownerDetails->id);
            
            if ($vehicleExists && $ownerExists) {
                $this->info('✅ SUCCESS: Owner Operator creation test passed!');
                $this->info('Vehicle ID: ' . $vehicleExists->id);
                $this->info('Owner Operator Detail ID: ' . $ownerExists->id);
                $this->info('Driver Application ID: ' . $application->id);
                
                DB::commit();
                return 0;
            } else {
                throw new \Exception('Failed to verify created records');
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ ERROR: ' . $e->getMessage());
            Log::error('Owner Operator creation test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}