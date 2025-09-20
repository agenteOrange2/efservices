<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Verificar todos los vehículos
echo "All Vehicles:" . PHP_EOL;
$vehicles = App\Models\Admin\Vehicle\Vehicle::all();
foreach ($vehicles as $vehicle) {
    echo "Vehicle ID: {$vehicle->id}, user_driver_detail_id: " . ($vehicle->user_driver_detail_id ?? 'NULL') . ", ownership_type: " . ($vehicle->ownership_type ?? 'NULL') . PHP_EOL;
}

echo "\n";

// Verificar el vehículo que tiene user_driver_detail_id = 4
$vehicleWithDriver4 = App\Models\Admin\Vehicle\Vehicle::where('user_driver_detail_id', 4)->first();
if ($vehicleWithDriver4) {
    echo "Vehicle with user_driver_detail_id = 4:" . PHP_EOL;
    echo "Vehicle ID: " . $vehicleWithDriver4->id . PHP_EOL;
    echo "ownership_type: " . ($vehicleWithDriver4->ownership_type ?? 'NULL') . PHP_EOL;
    
    // Verificar si existe el UserDriverDetail
    $userDriverDetail = App\Models\UserDriverDetail::find(4);
    echo "UserDriverDetail ID 4 exists: " . ($userDriverDetail ? 'YES' : 'NO') . PHP_EOL;
    
    if ($userDriverDetail) {
        echo "Driver name: " . ($userDriverDetail->user->name ?? 'N/A') . PHP_EOL;
    }
} else {
    echo "No vehicle found with user_driver_detail_id = 4" . PHP_EOL;
}

// Verificar todos los UserDriverDetail disponibles
echo "\nAvailable UserDriverDetails:" . PHP_EOL;
$userDriverDetails = App\Models\UserDriverDetail::with('user')->get();
foreach ($userDriverDetails as $detail) {
    echo "ID: {$detail->id}, User: " . ($detail->user->name ?? 'N/A') . PHP_EOL;
}