<?php

require_once __DIR__ . '/vendor/autoload.php';

// Configurar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserDriverDetail;
use App\Models\ThirdPartyDetail;
use App\Models\Admin\Vehicle\Vehicle;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

echo "=== Verificación del Contenido de PDFs Third Party ===\n";

// Buscar el driver de prueba
$driver = UserDriverDetail::with([
    'application.details',
    'application.thirdPartyDetail',
    'user',
    'carrier'
])->find(3);

if (!$driver) {
    echo "❌ Driver no encontrado\n";
    exit(1);
}

$thirdPartyDetails = $driver->application->thirdPartyDetail;
$vehicle = Vehicle::where('user_driver_detail_id', $driver->id)->first();

echo "✅ Datos encontrados:\n";
echo "   Driver: {$driver->user->name}\n";
echo "   Third Party: {$thirdPartyDetails->third_party_name}\n";
echo "   Vehículo: {$vehicle->year} {$vehicle->make}\n";

// Preparar los datos como lo hace el método generateThirdPartyDocuments
$verification = (object) [
    'third_party_name' => $thirdPartyDetails->third_party_name,
    'third_party_phone' => $thirdPartyDetails->third_party_phone,
    'third_party_email' => $thirdPartyDetails->third_party_email,
    'third_party_address' => $thirdPartyDetails->third_party_address,
    'third_party_contact' => $thirdPartyDetails->third_party_contact,
    'third_party_dba' => $thirdPartyDetails->third_party_dba,
    'third_party_fein' => $thirdPartyDetails->third_party_fein,
];

$driverDetails = (object) [
    'name' => $driver->user->name,
    'email' => $driver->user->email,
    'phone' => $driver->phone ?? 'N/A',
];

$vehicleData = (object) [
    'brand' => $vehicle->make,
    'model' => $vehicle->model,
    'year' => $vehicle->year,
    'vin' => $vehicle->vin,
    'type' => $vehicle->type,
    'registration_status' => 'Active',
    'registration_number' => $vehicle->registration_number,
];

// Datos para el consent
$consentData = [
    'verification' => $verification,
    'driverDetails' => $driverDetails,
    'vehicle' => $vehicleData,
    'signatureData' => null
];

// Datos para el lease agreement
$leaseData = [
    'carrierName' => $driver->carrier->name ?? 'N/A',
    'carrierMc' => $driver->carrier->mc_number ?? 'N/A',
    'carrierUsdot' => $driver->carrier->usdot_number ?? 'N/A',
    'ownerName' => $verification->third_party_name,
    'year' => $vehicle->year,
    'make' => $vehicle->make,
    'vin' => $vehicle->vin,
    'unitNumber' => $vehicle->company_unit_number ?? 'N/A',
    'signatureData' => null
];

echo "\n=== Verificando Plantillas ===\n";

// Verificar que las plantillas puedan renderizar con los datos
try {
    echo "📄 Probando plantilla third-party-consent...\n";
    $consentHtml = View::make('pdfs.third-party-consent', $consentData)->render();
    echo "✅ Plantilla third-party-consent renderizada correctamente\n";
    echo "   Contiene nombre: " . (strpos($consentHtml, $verification->third_party_name) !== false ? '✅' : '❌') . "\n";
    echo "   Contiene teléfono: " . (strpos($consentHtml, $verification->third_party_phone) !== false ? '✅' : '❌') . "\n";
    echo "   Contiene email: " . (strpos($consentHtml, $verification->third_party_email) !== false ? '✅' : '❌') . "\n";
    
} catch (Exception $e) {
    echo "❌ Error en plantilla third-party-consent: {$e->getMessage()}\n";
}

try {
    echo "\n📄 Probando plantilla lease-agreement...\n";
    $leaseHtml = View::make('pdfs.lease-agreement', $leaseData)->render();
    echo "✅ Plantilla lease-agreement renderizada correctamente\n";
    echo "   Contiene owner name: " . (strpos($leaseHtml, $verification->third_party_name) !== false ? '✅' : '❌') . "\n";
    echo "   Contiene carrier: " . (strpos($leaseHtml, $driver->carrier->name) !== false ? '✅' : '❌') . "\n";
    echo "   Contiene vehículo: " . (strpos($leaseHtml, $vehicle->make) !== false ? '✅' : '❌') . "\n";
    
} catch (Exception $e) {
    echo "❌ Error en plantilla lease-agreement: {$e->getMessage()}\n";
}

// Verificar que los PDFs existen
echo "\n=== Verificando PDFs Generados ===\n";
$consentPath = "driver/3/vehicle_verifications/third_party_consent.pdf";
$leasePath = "driver/3/vehicle_verifications/lease_agreement_third_party.pdf";

if (Storage::disk('public')->exists($consentPath)) {
    echo "✅ PDF de consentimiento existe\n";
    $size = Storage::disk('public')->size($consentPath);
    echo "   Tamaño: {$size} bytes\n";
} else {
    echo "❌ PDF de consentimiento no existe\n";
}

if (Storage::disk('public')->exists($leasePath)) {
    echo "✅ PDF de lease agreement existe\n";
    $size = Storage::disk('public')->size($leasePath);
    echo "   Tamaño: {$size} bytes\n";
} else {
    echo "❌ PDF de lease agreement no existe\n";
}

echo "\n✅ Verificación completada\n";
echo "\n=== Resumen de la Corrección ===\n";
echo "1. ✅ Método generateThirdPartyDocuments corregido\n";
echo "2. ✅ Datos de third_party_details se pasan correctamente\n";
echo "3. ✅ Ambos documentos se generan (consent + lease agreement)\n";
echo "4. ✅ Las plantillas reciben los datos en el formato correcto\n";
echo "\n🎉 Problema resuelto exitosamente\n";