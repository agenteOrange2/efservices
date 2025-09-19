<?php

require_once __DIR__ . '/vendor/autoload.php';

// Configurar el entorno Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserDriverDetail;
use App\Models\DriverApplication;
use App\Models\DriverApplicationDetail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

echo "=== Test Position Change Logic ===\n";

// Test con driver ID 3 (que sabemos que existe)
$driverId = 3;

echo "\n1. Verificando estado inicial del driver ID: $driverId\n";

$userDriverDetail = UserDriverDetail::find($driverId);
if (!$userDriverDetail) {
    echo "ERROR: Driver no encontrado\n";
    exit(1);
}

echo "Driver encontrado: {$userDriverDetail->user->name}\n";

$application = $userDriverDetail->application;
if (!$application) {
    echo "ERROR: No se encontró aplicación para este driver\n";
    exit(1);
}

echo "Aplicación encontrada ID: {$application->id}\n";

$applicationDetails = $application->details;
if ($applicationDetails) {
    echo "Applying position actual: {$applicationDetails->applying_position}\n";
} else {
    echo "No hay detalles de aplicación\n";
}

echo "\n2. Simulando cambio de posición a 'owner_operator'\n";

// Simular el cambio a owner_operator
DB::beginTransaction();

try {
    // Actualizar applying_position
    $applicationDetails = $application->details()->updateOrCreate(
        [],
        [
            'applying_position' => 'owner_operator',
            'applying_location' => 'test_location',
            'eligible_to_work' => true,
            'can_speak_english' => true,
            'has_twic_card' => false,
            'expected_pay' => 50000,
            'how_did_hear' => 'website',
            'has_work_history' => true,
        ]
    );
    
    echo "Detalles de aplicación actualizados\n";
    echo "Nuevo applying_position: {$applicationDetails->applying_position}\n";
    
    // Limpiar third party details y crear owner operator details
    $application->thirdPartyDetail()->delete();
    echo "Third party details eliminados\n";
    
    $ownerDetail = $application->ownerOperatorDetail()->updateOrCreate(
        [],
        [
            'owner_name' => 'Test Owner',
            'owner_phone' => '1234567890',
            'owner_email' => 'test@example.com',
            'contract_agreed' => true,
        ]
    );
    
    echo "Owner operator details creados/actualizados\n";
    
    DB::commit();
    echo "Transacción completada exitosamente\n";
    
} catch (Exception $e) {
    DB::rollback();
    echo "ERROR en transacción: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n3. Verificando el estado después del cambio\n";

// Recargar datos
$application->refresh();
$applicationDetails = $application->details;

if ($applicationDetails) {
    echo "Applying position después del cambio: {$applicationDetails->applying_position}\n";
} else {
    echo "ERROR: No se encontraron detalles de aplicación después del cambio\n";
}

$ownerDetail = $application->ownerOperatorDetail;
if ($ownerDetail) {
    echo "Owner operator detail encontrado: {$ownerDetail->owner_name}\n";
} else {
    echo "No se encontró owner operator detail\n";
}

$thirdPartyDetail = $application->thirdPartyDetail;
if ($thirdPartyDetail) {
    echo "WARNING: Third party detail aún existe: {$thirdPartyDetail->third_party_name}\n";
} else {
    echo "Third party detail correctamente eliminado\n";
}

echo "\n4. Simulando cambio a 'third_party_driver'\n";

DB::beginTransaction();

try {
    // Actualizar applying_position
    $applicationDetails = $application->details()->updateOrCreate(
        [],
        [
            'applying_position' => 'third_party_driver',
            'applying_location' => 'test_location',
            'eligible_to_work' => true,
            'can_speak_english' => true,
            'has_twic_card' => false,
            'expected_pay' => 50000,
            'how_did_hear' => 'website',
            'has_work_history' => true,
        ]
    );
    
    echo "Detalles de aplicación actualizados\n";
    echo "Nuevo applying_position: {$applicationDetails->applying_position}\n";
    
    // Limpiar owner operator details y crear third party details
    $application->ownerOperatorDetail()->delete();
    echo "Owner operator details eliminados\n";
    
    $thirdPartyDetail = $application->thirdPartyDetail()->updateOrCreate(
        [],
        [
            'third_party_name' => 'Test Third Party',
            'third_party_phone' => '0987654321',
            'third_party_email' => 'thirdparty@example.com',
            'third_party_dba' => 'Test DBA',
            'third_party_address' => 'Test Address',
            'third_party_contact' => 'Test Contact',
            'third_party_fein' => '123456789',
            'email_sent' => false,
        ]
    );
    
    echo "Third party details creados/actualizados\n";
    
    DB::commit();
    echo "Transacción completada exitosamente\n";
    
} catch (Exception $e) {
    DB::rollback();
    echo "ERROR en transacción: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n5. Verificando el estado final\n";

// Recargar datos
$application->refresh();
$applicationDetails = $application->details;

if ($applicationDetails) {
    echo "Applying position final: {$applicationDetails->applying_position}\n";
} else {
    echo "ERROR: No se encontraron detalles de aplicación después del cambio\n";
}

$ownerDetail = $application->ownerOperatorDetail;
if ($ownerDetail) {
    echo "WARNING: Owner operator detail aún existe: {$ownerDetail->owner_name}\n";
} else {
    echo "Owner operator detail correctamente eliminado\n";
}

$thirdPartyDetail = $application->thirdPartyDetail;
if ($thirdPartyDetail) {
    echo "Third party detail encontrado: {$thirdPartyDetail->third_party_name}\n";
} else {
    echo "No se encontró third party detail\n";
}

echo "\n=== Test completado ===\n";