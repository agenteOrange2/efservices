<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\UserDriverDetail;
use App\Models\DriverApplication;
use App\Models\DriverApplicationDetail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

echo "=== PRUEBA COMPLETA DEL FLUJO ===\n\n";

// Test con driver ID 4 (que sabemos tiene applying_position = owner_operator)
$driverId = 4;

echo "1. Verificando datos actuales del driver {$driverId}:\n";
$userDriverDetail = UserDriverDetail::find($driverId);
if (!$userDriverDetail) {
    echo "Error: Driver no encontrado\n";
    exit(1);
}

$application = $userDriverDetail->application;
if (!$application) {
    echo "Error: Aplicación no encontrada\n";
    exit(1);
}

$details = $application->details;
if (!$details) {
    echo "Error: Detalles de aplicación no encontrados\n";
    exit(1);
}

echo "   - Driver: {$userDriverDetail->user->name}\n";
echo "   - Aplicación ID: {$application->id}\n";
echo "   - Applying position actual: {$details->applying_position}\n";
echo "   - User ID: {$application->user_id}\n\n";

// 2. Simular cambio a third_party_driver
echo "2. Simulando cambio a 'third_party_driver':\n";
$details->update(['applying_position' => 'third_party_driver']);
echo "   - Applying position actualizado a: third_party_driver\n";

// Verificar que se guardó
$details->refresh();
echo "   - Verificación: {$details->applying_position}\n\n";

// 3. Simular lógica de CertificationStep
echo "3. Simulando lógica de CertificationStep:\n";
$userDriverDetail->load(['application.details']);
$application = $userDriverDetail->application;
$applicationDetails = $application ? $application->details : null;

if (!$applicationDetails) {
    echo "   - ERROR: No se encontraron detalles de aplicación\n";
} else {
    $applyingPosition = $applicationDetails->applying_position ?? 'unknown';
    echo "   - Applying position leído: {$applyingPosition}\n";
    
    if ($applyingPosition === 'owner_operator') {
        echo "   - Resultado: Debería generar Lease Agreement (Owner Operator)\n";
    } elseif ($applyingPosition === 'third_party_driver') {
        echo "   - Resultado: Debería generar Third Party Documents\n";
    } else {
        echo "   - Resultado: Tipo no reconocido - {$applyingPosition}\n";
    }
}

echo "\n4. Cambiando de vuelta a 'owner_operator':\n";
$details->update(['applying_position' => 'owner_operator']);
echo "   - Applying position actualizado a: owner_operator\n";

// Verificar que se guardó
$details->refresh();
echo "   - Verificación: {$details->applying_position}\n\n";

// 5. Simular lógica de CertificationStep otra vez
echo "5. Simulando lógica de CertificationStep nuevamente:\n";
$userDriverDetail->load(['application.details']);
$application = $userDriverDetail->application;
$applicationDetails = $application ? $application->details : null;

if (!$applicationDetails) {
    echo "   - ERROR: No se encontraron detalles de aplicación\n";
} else {
    $applyingPosition = $applicationDetails->applying_position ?? 'unknown';
    echo "   - Applying position leído: {$applyingPosition}\n";
    
    if ($applyingPosition === 'owner_operator') {
        echo "   - Resultado: Debería generar Lease Agreement (Owner Operator)\n";
    } elseif ($applyingPosition === 'third_party_driver') {
        echo "   - Resultado: Debería generar Third Party Documents\n";
    } else {
        echo "   - Resultado: Tipo no reconocido - {$applyingPosition}\n";
    }
}

echo "\n=== PRUEBA COMPLETADA ===\n";