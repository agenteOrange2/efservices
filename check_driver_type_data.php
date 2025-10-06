<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Driver\DriverApplicationDetail;
use App\Models\OwnerOperatorDetail;
use App\Models\ThirdPartyDetail;
use App\Models\Admin\Vehicle\Vehicle;

echo "=== ANÁLISIS DE DATOS DRIVER TYPES ===\n\n";

// 1. Verificar DriverApplications
echo "1. DRIVER APPLICATIONS:\n";
$driverApps = DriverApplication::all();
echo "Total: " . $driverApps->count() . "\n";
foreach($driverApps as $app) {
    echo "  - ID: {$app->id}, User: {$app->user_id}, Status: {$app->status}, Created: {$app->created_at}\n";
}

// 2. Verificar DriverApplicationDetails
echo "\n2. DRIVER APPLICATION DETAILS:\n";
$details = DriverApplicationDetail::with('vehicle')->get();
echo "Total: " . $details->count() . "\n";
foreach($details as $detail) {
    $vehicleInfo = $detail->vehicle ? "Vehicle {$detail->vehicle->id} ({$detail->vehicle->make} {$detail->vehicle->model})" : "No Vehicle";
    echo "  - ID: {$detail->id}, App: {$detail->driver_application_id}, Vehicle: {$detail->vehicle_id}, Position: {$detail->applying_position}, {$vehicleInfo}\n";
}

// 3. Verificar OwnerOperatorDetails
echo "\n3. OWNER OPERATOR DETAILS:\n";
$ownerDetails = OwnerOperatorDetail::all();
echo "Total: " . $ownerDetails->count() . "\n";
foreach($ownerDetails as $owner) {
    echo "  - ID: {$owner->id}, App: {$owner->driver_application_id}, Vehicle: {$owner->vehicle_id}, Name: {$owner->owner_name}\n";
}

// 4. Verificar ThirdPartyDetails
echo "\n4. THIRD PARTY DETAILS:\n";
$thirdPartyDetails = ThirdPartyDetail::all();
echo "Total: " . $thirdPartyDetails->count() . "\n";
foreach($thirdPartyDetails as $third) {
    echo "  - ID: {$third->id}, App: {$third->driver_application_id}, Vehicle: {$third->vehicle_id}, Name: {$third->third_party_name}\n";
}

// 5. Verificar qué aplicaciones tienen detalles (como busca DriverTypeController)
echo "\n5. APLICACIONES CON DETALLES (como busca DriverTypeController):\n";
$appsWithDetails = DriverApplication::with(['details', 'ownerOperatorDetail', 'thirdPartyDetail'])
    ->whereHas('details')
    ->get();
echo "Total encontradas: " . $appsWithDetails->count() . "\n";
foreach($appsWithDetails as $app) {
    $hasOwner = $app->ownerOperatorDetail ? 'YES' : 'NO';
    $hasThird = $app->thirdPartyDetail ? 'YES' : 'NO';
    $vehicleId = $app->details ? $app->details->vehicle_id : 'N/A';
    echo "  - App ID: {$app->id}, Vehicle: {$vehicleId}, Owner: {$hasOwner}, ThirdParty: {$hasThird}\n";
}

// 6. Verificar vehículos con ownership_type
echo "\n6. VEHÍCULOS CON OWNERSHIP_TYPE:\n";
$vehicles = Vehicle::whereNotNull('ownership_type')->get();
echo "Total: " . $vehicles->count() . "\n";
foreach($vehicles as $vehicle) {
    echo "  - Vehicle ID: {$vehicle->id}, Unit: {$vehicle->company_unit_number}, Ownership: {$vehicle->ownership_type}\n";
}

echo "\n=== FIN DEL ANÁLISIS ===\n";