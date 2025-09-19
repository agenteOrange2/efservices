<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Admin\Driver\UserDriverDetail;
use App\Models\Admin\Vehicle\Vehicle;
use Illuminate\Support\Facades\Log;

try {
    echo "Testing vehicle creation...\n";
    
    // Get first user driver detail
    $userDriverDetail = UserDriverDetail::first();
    
    if (!$userDriverDetail) {
        echo "No UserDriverDetail found\n";
        exit(1);
    }
    
    echo "Found UserDriverDetail ID: {$userDriverDetail->id}\n";
    echo "Carrier ID: {$userDriverDetail->carrier_id}\n";
    
    // Test vehicle creation
    $vehicleData = [
        'make' => 'Test Make',
        'model' => 'Test Model', 
        'year' => 2020,
        'vin' => '1HGBH41JXMN109187', // Different VIN to avoid conflicts
        'carrier_id' => $userDriverDetail->carrier_id,
        'user_driver_detail_id' => $userDriverDetail->id
    ];
    
    echo "Creating vehicle with data: " . json_encode($vehicleData) . "\n";
    
    $vehicle = Vehicle::create($vehicleData);
    
    echo "Vehicle created successfully with ID: {$vehicle->id}\n";
    echo "Vehicle VIN: {$vehicle->vin}\n";
    
    // Verify vehicle exists
    $foundVehicle = Vehicle::find($vehicle->id);
    if ($foundVehicle) {
        echo "Vehicle verification: SUCCESS\n";
    } else {
        echo "Vehicle verification: FAILED\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}