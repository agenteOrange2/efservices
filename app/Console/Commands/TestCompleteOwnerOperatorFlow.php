<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\OwnerOperatorDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestCompleteOwnerOperatorFlow extends Command
{
    protected $signature = 'test:complete-owner-operator-flow';
    protected $description = 'Test complete Owner Operator flow including foreign key constraints';

    public function handle()
    {
        $this->info('Testing complete Owner Operator flow...');
        
        try {
            DB::beginTransaction();
            
            // Buscar un UserDriverDetail existente
            $userDriverDetail = UserDriverDetail::first();
            if (!$userDriverDetail) {
                $this->error('No UserDriverDetail found. Please create a driver first.');
                return 1;
            }
            
            $this->info('Step 1: Using driver ID: ' . $userDriverDetail->id);
            
            // Crear aplicación
            $application = DriverApplication::create([
                'user_id' => $userDriverDetail->user_id,
                'status' => 'draft'
            ]);
            $this->info('Step 2: Created application ID: ' . $application->id);
            
            // Crear vehículo PRIMERO
            $vehicleData = [
                'make' => 'Peterbilt',
                'model' => '579',
                'year' => 2021,
                'vin' => 'FLOW' . time() . 'TEST123',
                'type' => 'tractor',
                'fuel_type' => 'diesel',
                'registration_state' => 'CA',
                'registration_number' => 'FLOW' . time(),
                'registration_expiration_date' => '2025-12-31',
                'driver_type' => 'owner_operator',
                'status' => 'active',
                'carrier_id' => $userDriverDetail->carrier_id,
                'user_driver_detail_id' => $userDriverDetail->id,
            ];
            
            $vehicle = Vehicle::create($vehicleData);
            $this->info('Step 3: Created vehicle ID: ' . $vehicle->id);
            
            // Verificar que el vehículo existe antes de crear owner_operator_details
            $vehicleCheck = Vehicle::find($vehicle->id);
            if (!$vehicleCheck) {
                throw new \Exception('Vehicle was not created properly');
            }
            $this->info('Step 4: Vehicle verification passed');
            
            // Ahora crear Owner Operator Detail con todas las claves foráneas correctas
            $ownerOperatorData = [
                'driver_application_id' => $application->id,
                'vehicle_id' => $vehicle->id,
                'owner_name' => 'Complete Flow Test Owner',
                'owner_phone' => '9876543210',
                'owner_email' => 'flowtest@example.com',
                'contract_agreed' => true,
            ];
            
            // Usar el método correcto con las claves foráneas
            $ownerDetails = OwnerOperatorDetail::create($ownerOperatorData);
            $this->info('Step 5: Created Owner Operator Detail ID: ' . $ownerDetails->id);
            
            // Verificaciones finales
            $finalVehicleCheck = Vehicle::find($vehicle->id);
            $finalOwnerCheck = OwnerOperatorDetail::find($ownerDetails->id);
            $applicationCheck = DriverApplication::find($application->id);
            
            if ($finalVehicleCheck && $finalOwnerCheck && $applicationCheck) {
                $this->info('✅ SUCCESS: Complete Owner Operator flow test passed!');
                $this->info('Final Results:');
                $this->info('- Vehicle ID: ' . $finalVehicleCheck->id . ' (VIN: ' . $finalVehicleCheck->vin . ')');
                $this->info('- Owner Operator Detail ID: ' . $finalOwnerCheck->id);
                $this->info('- Driver Application ID: ' . $applicationCheck->id);
                $this->info('- All foreign key constraints satisfied');
                
                // Verificar las relaciones
                $ownerVehicle = $finalOwnerCheck->vehicle;
                $ownerApplication = $finalOwnerCheck->driverApplication;
                
                if ($ownerVehicle && $ownerApplication) {
                    $this->info('- ✅ All relationships working correctly');
                } else {
                    $this->warn('- ⚠️  Some relationships may have issues');
                }
                
                DB::commit();
                return 0;
            } else {
                throw new \Exception('Failed final verification checks');
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ ERROR: ' . $e->getMessage());
            $this->error('Error details: ' . $e->getFile() . ':' . $e->getLine());
            Log::error('Complete Owner Operator flow test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}