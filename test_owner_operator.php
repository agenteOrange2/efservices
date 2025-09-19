<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Driver\DriverApplicationDetail;
use Illuminate\Support\Facades\DB;

echo "=== CREANDO DRIVER DE PRUEBA CON OWNER_OPERATOR ===\n";

// Buscar un driver existente para modificar temporalmente
$driver = UserDriverDetail::with(['application.details'])->find(4);

if (!$driver || !$driver->application || !$driver->application->details) {
    echo "✗ No se puede usar el driver ID 4 para la prueba\n";
    exit(1);
}

echo "Driver ID: " . $driver->id . "\n";
echo "applying_position actual: " . $driver->application->details->applying_position . "\n";

// Guardar el valor original
$originalPosition = $driver->application->details->applying_position;

// Cambiar temporalmente a owner_operator
echo "\n=== CAMBIANDO TEMPORALMENTE A OWNER_OPERATOR ===\n";
$driver->application->details->update(['applying_position' => 'owner_operator']);

// Verificar el cambio
$driver->refresh();
echo "applying_position después del cambio: " . $driver->application->details->applying_position . "\n";

// Simular la lógica de DriverRecruitmentReview.php
echo "\n=== SIMULANDO LÓGICA DE GENERACIÓN DE DOCUMENTOS ===\n";

$userDriverDetail = $driver;
$application = $userDriverDetail->application;

if ($application && $application->details) {
    $applyingPosition = $application->details->applying_position;
    echo "applying_position obtenido: '" . $applyingPosition . "'\n";
    
    if ($applyingPosition === 'owner_operator') {
        echo "✓ DEBERÍA generar documento de OWNER OPERATOR\n";
        echo "  - Llamaría a generateLeaseAgreementOwner()\n";
    } elseif ($applyingPosition === 'third_party_driver') {
        echo "✓ DEBERÍA generar documento de THIRD PARTY DRIVER\n";
        echo "  - Llamaría a generateThirdPartyDocuments()\n";
    } else {
        echo "✗ applying_position no reconocido: '" . $applyingPosition . "'\n";
    }
} else {
    echo "✗ No se pueden obtener los datos de aplicación\n";
}

// Restaurar el valor original
echo "\n=== RESTAURANDO VALOR ORIGINAL ===\n";
$driver->application->details->update(['applying_position' => $originalPosition]);
$driver->refresh();
echo "applying_position restaurado a: " . $driver->application->details->applying_position . "\n";

echo "\n=== PRUEBA COMPLETADA ===\n";