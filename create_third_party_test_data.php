<?php

require_once __DIR__ . '/vendor/autoload.php';

// Configurar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserDriverDetail;
use App\Models\DriverApplication;
use App\Models\ThirdPartyDetail;
use App\Models\ApplicationDetail;
use App\Models\Admin\Vehicle\Vehicle;
use Illuminate\Support\Facades\DB;

echo "=== Creando Datos de Prueba para Third Party Driver ===\n";

// Buscar el driver ID 3
$driver = UserDriverDetail::with(['application', 'user'])->find(3);

if (!$driver) {
    echo "❌ Driver ID 3 no encontrado\n";
    exit(1);
}

echo "✅ Driver encontrado: {$driver->user->name}\n";

// Verificar si tiene aplicación
if (!$driver->application) {
    echo "❌ El driver no tiene aplicación\n";
    exit(1);
}

echo "✅ Aplicación encontrada: ID {$driver->application->id}\n";

// Verificar el applying_position actual
$applicationDetails = $driver->application->details;
if ($applicationDetails) {
    echo "✅ Application Details encontrados: applying_position = {$applicationDetails->applying_position}\n";
    
    // Si no es third_party_driver, cambiarlo
    if ($applicationDetails->applying_position !== 'third_party_driver') {
        echo "🔄 Cambiando applying_position a 'third_party_driver'...\n";
        $applicationDetails->applying_position = 'third_party_driver';
        $applicationDetails->save();
        echo "✅ applying_position actualizado\n";
    }
} else {
    echo "❌ No se encontraron application details\n";
    exit(1);
}

// Verificar si ya tiene third_party_details
$thirdPartyDetails = $driver->application->thirdPartyDetail;

if ($thirdPartyDetails) {
    echo "✅ Third Party Details ya existen\n";
    echo "   - Nombre: {$thirdPartyDetails->third_party_name}\n";
    echo "   - Teléfono: {$thirdPartyDetails->third_party_phone}\n";
    echo "   - Email: {$thirdPartyDetails->third_party_email}\n";
} else {
    echo "🔄 Creando Third Party Details...\n";
    
    $thirdPartyDetails = ThirdPartyDetail::create([
        'driver_application_id' => $driver->application->id,
        'third_party_name' => 'ABC Transport Company',
        'third_party_dba' => 'ABC Transport',
        'third_party_address' => '123 Main Street, Houston, TX 77001',
        'third_party_phone' => '(713) 555-0123',
        'third_party_email' => 'contact@abctransport.com',
        'third_party_contact' => 'John Smith',
        'third_party_fein' => '12-3456789'
    ]);
    
    echo "✅ Third Party Details creados:\n";
    echo "   - Nombre: {$thirdPartyDetails->third_party_name}\n";
    echo "   - Teléfono: {$thirdPartyDetails->third_party_phone}\n";
    echo "   - Email: {$thirdPartyDetails->third_party_email}\n";
}

// Verificar que tenga vehículo
$vehicle = Vehicle::where('user_driver_detail_id', $driver->id)->first();
if (!$vehicle) {
    echo "🔄 Creando vehículo de prueba...\n";
    
    $vehicle = Vehicle::create([
        'user_driver_detail_id' => $driver->id,
        'carrier_id' => $driver->carrier_id,
        'year' => '2020',
        'make' => 'Freightliner',
        'model' => 'Cascadia',
        'vin' => '1FUJGHDV8LLBX1234',
        'type' => 'truck',
        'company_unit_number' => 'TP001',
        'registration_state' => 'TX',
        'registration_number' => 'ABC123TX',
        'registration_expiration_date' => '2025-12-31',
        'fuel_type' => 'diesel'
    ]);
    
    echo "✅ Vehículo creado: {$vehicle->year} {$vehicle->make}\n";
} else {
    echo "✅ Vehículo ya existe: {$vehicle->year} {$vehicle->make}\n";
}

echo "\n✅ Datos de prueba preparados correctamente\n";
echo "Driver ID: {$driver->id}\n";
echo "Application ID: {$driver->application->id}\n";
echo "Third Party Details ID: {$thirdPartyDetails->id}\n";
echo "Vehicle ID: {$vehicle->id}\n";
echo "\n=== Listo para probar generación de PDFs ===\n";