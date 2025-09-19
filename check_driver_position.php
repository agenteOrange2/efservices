<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Driver\DriverApplicationDetail;
use App\Models\OwnerOperatorDetail;
use App\Models\ThirdPartyDetail;

echo "=== Verificando Driver ID 4 ===\n";

// Encontrar el driver
$driver = UserDriverDetail::find(4);
if (!$driver) {
    echo "Driver ID 4 no encontrado\n";
    exit(1);
}

echo "Driver encontrado: {$driver->first_name}\n";

// Buscar la aplicación del driver
$driverApplication = DriverApplication::where('user_id', $driver->user_id)->first();
if (!$driverApplication) {
    echo "DriverApplication no encontrada para driver ID 4\n";
    exit(1);
}

echo "Driver Application ID: {$driverApplication->id}\n";
echo "Application Status: {$driverApplication->status}\n";

// Buscar los detalles de la aplicación
$applicationDetails = DriverApplicationDetail::where('driver_application_id', $driverApplication->id)->first();
if (!$applicationDetails) {
    echo "DriverApplicationDetail no encontrado para application ID {$driverApplication->id}\n";
    exit(1);
}

echo "Details ID: {$applicationDetails->id}\n";
echo "Applying Position en DB: '{$applicationDetails->applying_position}'\n";

// Verificar detalles de Owner Operator
$ownerDetails = OwnerOperatorDetail::where('driver_application_id', $driverApplication->id)->first();
echo "Owner Operator Details encontrados: " . ($ownerDetails ? $ownerDetails->owner_name : 'NO') . "\n";

// Verificar detalles de Third Party
$thirdPartyDetails = ThirdPartyDetail::where('driver_application_id', $driverApplication->id)->first();
echo "Third Party Details encontrados: " . ($thirdPartyDetails ? $thirdPartyDetails->third_party_name : 'NO') . "\n";