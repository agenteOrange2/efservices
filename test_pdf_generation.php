<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Driver\DriverApplicationDetail;
use App\Models\Admin\Driver\DriverDetail;
use App\Models\OwnerOperatorDetail;
use App\Models\ThirdPartyDetail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

echo "=== VERIFICANDO GENERACIÓN DE PDFs ===\n";

// Probar con driver ID 4 (Walter) que debería ser owner_operator
$driverId = 4;
echo "\nProbando con driver ID: $driverId\n";

$driver = UserDriverDetail::find($driverId);
if (!$driver) {
    echo "Driver no encontrado\n";
    exit(1);
}

echo "Driver encontrado: {$driver->user->name}\n";

// Obtener la aplicación del driver
$driverApplication = DriverApplication::where('user_id', $driver->user_id)->first();
if (!$driverApplication) {
    echo "No se encontró aplicación para el driver\n";
    exit(1);
}

echo "Aplicación encontrada - ID: {$driverApplication->id}\n";

// Verificar detalles de la aplicación
$details = $driverApplication->details;
if (!$details) {
    echo "Error: No se encontraron detalles de la aplicación\n";
    exit(1);
}

echo "Tipo de aplicación: {$details->applying_position}\n";

// Verificar el driver detail asociado (por user_id)
$driverDetail = UserDriverDetail::where('user_id', $driverApplication->user_id)->first();
if (!$driverDetail) {
    echo "Error: No se encontró driver detail para el usuario {$driverApplication->user_id}\n";
    exit(1);
}

echo "Driver detail encontrado - ID: {$driverDetail->id}\n";
echo "Applying position desde details: {$details->applying_position}\n";

// Verificar detalles según el tipo
if ($details->applying_position === 'owner_operator') {
    $ownerDetails = OwnerOperatorDetail::where('driver_application_id', $driverApplication->id)->first();
    if ($ownerDetails) {
        echo "Detalles de owner operator encontrados - ID: {$ownerDetails->id}\n";
        echo "Owner name: {$ownerDetails->owner_name}\n";
        echo "Owner email: {$ownerDetails->owner_email}\n";
        echo "Owner phone: {$ownerDetails->owner_phone}\n";
    } else {
        echo "No se encontraron detalles de owner operator\n";
    }
    
    // Verificar que no haya detalles de third party
    $thirdPartyDetails = ThirdPartyDetail::where('driver_application_id', $driverApplication->id)->first();
    if ($thirdPartyDetails) {
        echo "ADVERTENCIA: Se encontraron detalles de third party cuando debería ser owner operator\n";
    } else {
        echo "Correcto: No hay detalles de third party\n";
    }
    
} elseif ($details->applying_position === 'third_party_driver') {
    $thirdPartyDetails = ThirdPartyDetail::where('driver_application_id', $driverApplication->id)->first();
    if ($thirdPartyDetails) {
        echo "Detalles de third party encontrados - ID: {$thirdPartyDetails->id}\n";
        echo "Third party name: {$thirdPartyDetails->third_party_name}\n";
        echo "Third party email: {$thirdPartyDetails->third_party_email}\n";
        echo "Third party phone: {$thirdPartyDetails->third_party_phone}\n";
    } else {
        echo "No se encontraron detalles de third party\n";
    }
    
    // Verificar que no haya detalles de owner operator
    $ownerDetails = OwnerOperatorDetail::where('driver_application_id', $driverApplication->id)->first();
    if ($ownerDetails) {
        echo "ADVERTENCIA: Se encontraron detalles de owner operator cuando debería ser third party\n";
    } else {
        echo "Correcto: No hay detalles de owner operator\n";
    }
} else {
    echo "Tipo de aplicación desconocido: {$details->applying_position}\n";
}

// Simular la lógica de CertificationStep.php
echo "\n=== SIMULANDO LÓGICA DE CERTIFICACIÓN ===\n";

if ($details->applying_position === 'owner_operator') {
    echo "Debería generar: Lease Agreement para Owner Operator\n";
    echo "Método a llamar: generateLeaseAgreementPDF()\n";
} elseif ($details->applying_position === 'third_party_driver') {
    echo "Debería generar: Documentos de Third Party\n";
    echo "Método a llamar: generateThirdPartyDocuments()\n";
    echo "PDFs esperados: third_party_consent.pdf y lease_agreement_third_party.pdf\n";
} else {
    echo "PROBLEMA: Tipo de aplicación no reconocido\n";
}

echo "\n=== VERIFICANDO ARCHIVOS PDF EXISTENTES ===\n";

$publicPath = storage_path('app/public/driver/' . $driverId . '/vehicle_verifications/');
echo "Buscando PDFs en: $publicPath\n";

if (is_dir($publicPath)) {
    $files = scandir($publicPath);
    $pdfFiles = array_filter($files, function($file) {
        return pathinfo($file, PATHINFO_EXTENSION) === 'pdf';
    });
    
    if (!empty($pdfFiles)) {
        echo "PDFs encontrados:\n";
        foreach ($pdfFiles as $file) {
            $filePath = $publicPath . $file;
            $fileSize = filesize($filePath);
            $fileDate = date('Y-m-d H:i:s', filemtime($filePath));
            echo "  - $file (Tamaño: $fileSize bytes, Fecha: $fileDate)\n";
        }
    } else {
        echo "No se encontraron archivos PDF\n";
    }
} else {
    echo "Directorio no existe: $publicPath\n";
}

echo "\n=== PRUEBA COMPLETADA ===\n";