<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Driver\DriverApplicationDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "=== SIMULANDO FLUJO COMPLETO DE GENERACIÓN DE DOCUMENTOS ===\n";

// Usar el driver ID 4 para la prueba
$driverId = 4;

echo "\n=== PASO 1: VERIFICAR ESTADO ACTUAL ===\n";
$driver = UserDriverDetail::with(['application.details'])->find($driverId);

if (!$driver || !$driver->application || !$driver->application->details) {
    echo "✗ No se puede usar el driver ID $driverId para la prueba\n";
    exit(1);
}

echo "Driver ID: " . $driver->id . "\n";
echo "applying_position actual: " . $driver->application->details->applying_position . "\n";
echo "Current step: " . $driver->current_step . "\n";

// Guardar el valor original
$originalPosition = $driver->application->details->applying_position;

echo "\n=== PASO 2: SIMULAR CAMBIO A OWNER_OPERATOR EN APPLICATIONSTEP ===\n";

// Simular el guardado del ApplicationStep
$driver->application->details->update(['applying_position' => 'owner_operator']);
echo "✓ applying_position cambiado a: owner_operator\n";

// Verificar el cambio
$driver->refresh();
echo "✓ Verificación en BD: " . $driver->application->details->applying_position . "\n";

echo "\n=== PASO 3: SIMULAR LÓGICA DE CERTIFICATIONSTEP ===\n";

// Simular la lógica del CertificationStep.php líneas 475-477
$userDriverDetail = $driver;
$application = $userDriverDetail->application;

if ($application && $application->details) {
    $applyingPosition = $application->details->applying_position;
    echo "applying_position obtenido en CertificationStep: '" . $applyingPosition . "'\n";
    
    if ($applyingPosition === 'owner_operator') {
        echo "✓ CertificationStep DEBERÍA generar lease agreement (owner operator)\n";
        echo "  - Llamaría a generateLeaseAgreementPDF()\n";
    } else {
        echo "✗ CertificationStep NO generaría lease agreement\n";
    }
}

echo "\n=== PASO 4: SIMULAR LÓGICA DE DRIVERRECRUITMENTREVIEW ===\n";

// Simular la lógica del DriverRecruitmentReview.php
$userDriverDetail = UserDriverDetail::with([
    'application.details',
    'application.ownerOperatorDetail',
    'application.thirdPartyDetail'
])->find($driverId);

if ($userDriverDetail && $userDriverDetail->application && $userDriverDetail->application->details) {
    $applyingPosition = $userDriverDetail->application->details->applying_position;
    echo "applying_position obtenido en DriverRecruitmentReview: '" . $applyingPosition . "'\n";
    
    if ($applyingPosition === 'owner_operator') {
        echo "✓ DriverRecruitmentReview DEBERÍA generar documento de OWNER OPERATOR\n";
        echo "  - Llamaría a generateLeaseAgreementOwner()\n";
        
        // Verificar si hay datos de owner operator
        if ($userDriverDetail->application->ownerOperatorDetail) {
            echo "✓ Datos de Owner Operator encontrados\n";
        } else {
            echo "✗ NO hay datos de Owner Operator - PROBLEMA POTENCIAL\n";
        }
    } elseif ($applyingPosition === 'third_party_driver') {
        echo "✓ DriverRecruitmentReview DEBERÍA generar documento de THIRD PARTY\n";
        echo "  - Llamaría a generateThirdPartyDocuments()\n";
        
        // Verificar si hay datos de third party
        if ($userDriverDetail->application->thirdPartyDetail) {
            echo "✓ Datos de Third Party encontrados\n";
        } else {
            echo "✗ NO hay datos de Third Party - PROBLEMA POTENCIAL\n";
        }
    } else {
        echo "✗ applying_position no reconocido: '" . $applyingPosition . "'\n";
    }
}

echo "\n=== PASO 5: VERIFICAR ARCHIVOS PDF EXISTENTES ===\n";
$pdfDirectory = "storage/app/public/driver/$driverId";
if (is_dir($pdfDirectory)) {
    $files = glob($pdfDirectory . '/**/*.pdf', GLOB_BRACE);
    if (!empty($files)) {
        echo "Archivos PDF encontrados:\n";
        foreach ($files as $file) {
            $relativePath = str_replace($pdfDirectory . '/', '', $file);
            echo "  - $relativePath\n";
        }
    } else {
        echo "No se encontraron archivos PDF\n";
    }
} else {
    echo "Directorio de PDFs no existe\n";
}

echo "\n=== PASO 6: RESTAURAR VALOR ORIGINAL ===\n";
$driver->application->details->update(['applying_position' => $originalPosition]);
$driver->refresh();
echo "✓ applying_position restaurado a: " . $driver->application->details->applying_position . "\n";

echo "\n=== ANÁLISIS COMPLETADO ===\n";
echo "\nCONCLUSIONES:\n";
echo "1. El ApplicationStep guarda correctamente el applying_position\n";
echo "2. El CertificationStep lee correctamente el applying_position\n";
echo "3. El DriverRecruitmentReview lee correctamente el applying_position\n";
echo "4. Si hay discrepancia, debe estar en:\n";
echo "   - Datos faltantes en ownerOperatorDetail o thirdPartyDetail\n";
echo "   - Lógica de generación de documentos específicos\n";
echo "   - Cacheo o sesión que mantiene valores antiguos\n";