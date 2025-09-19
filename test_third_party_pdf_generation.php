<?php

require_once __DIR__ . '/vendor/autoload.php';

// Configurar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserDriverDetail;
use App\Models\DriverApplication;
use App\Models\ThirdPartyDetail;
use App\Models\Admin\Vehicle\Vehicle;
use App\Livewire\Driver\Steps\CertificationStep;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

echo "=== Prueba de Generación de PDFs para Third Party Driver ===\n";

// Buscar un driver con applying_position = 'third_party_driver'
$driver = UserDriverDetail::whereHas('application.details', function($query) {
    $query->where('applying_position', 'third_party_driver');
})->with([
    'application.details',
    'application.thirdPartyDetail',
    'user',
    'carrier'
])->first();

if (!$driver) {
    echo "❌ No se encontró ningún driver con applying_position = 'third_party_driver'\n";
    exit(1);
}

echo "✅ Driver encontrado: ID {$driver->id}, Usuario: {$driver->user->name}\n";

// Verificar que tenga third_party_details
$thirdPartyDetails = $driver->application->thirdPartyDetail;
if (!$thirdPartyDetails) {
    echo "❌ El driver no tiene third_party_details\n";
    exit(1);
}

echo "✅ Third Party Details encontrados:\n";
echo "   - Nombre: {$thirdPartyDetails->third_party_name}\n";
echo "   - Teléfono: {$thirdPartyDetails->third_party_phone}\n";
echo "   - Email: {$thirdPartyDetails->third_party_email}\n";

// Verificar que tenga vehículo
$vehicle = Vehicle::where('user_driver_detail_id', $driver->id)->first();
if (!$vehicle) {
    echo "❌ El driver no tiene vehículo asociado\n";
    exit(1);
}

echo "✅ Vehículo encontrado: {$vehicle->year} {$vehicle->make} - VIN: {$vehicle->vin}\n";

// Crear una instancia de CertificationStep para probar la generación
echo "\n=== Probando Generación de PDFs ===\n";

// Crear una firma temporal para la prueba
$signaturePath = storage_path('app/public/temp_signature_test.png');
if (!file_exists($signaturePath)) {
    // Crear una imagen simple de prueba
    $image = imagecreate(200, 100);
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    imagestring($image, 5, 50, 40, 'Test Signature', $black);
    imagepng($image, $signaturePath);
    imagedestroy($image);
}

try {
    // Usar reflexión para acceder al método privado generateThirdPartyDocuments
    $certificationStep = new CertificationStep();
    $reflection = new ReflectionClass($certificationStep);
    $method = $reflection->getMethod('generateThirdPartyDocuments');
    $method->setAccessible(true);
    
    echo "📄 Generando documentos para third-party driver...\n";
    $method->invoke($certificationStep, $driver, $signaturePath);
    
    // Verificar que se generaron los archivos
    $driverId = $driver->id;
    $consentPath = "driver/{$driverId}/vehicle_verifications/third_party_consent.pdf";
    $leasePath = "driver/{$driverId}/vehicle_verifications/lease_agreement_third_party.pdf";
    
    if (Storage::disk('public')->exists($consentPath)) {
        echo "✅ PDF de consentimiento generado: {$consentPath}\n";
        $consentSize = Storage::disk('public')->size($consentPath);
        echo "   Tamaño: {$consentSize} bytes\n";
    } else {
        echo "❌ PDF de consentimiento NO generado\n";
    }
    
    if (Storage::disk('public')->exists($leasePath)) {
        echo "✅ PDF de contrato de arrendamiento generado: {$leasePath}\n";
        $leaseSize = Storage::disk('public')->size($leasePath);
        echo "   Tamaño: {$leaseSize} bytes\n";
    } else {
        echo "❌ PDF de contrato de arrendamiento NO generado\n";
    }
    
    echo "\n✅ Prueba completada exitosamente\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la generación: {$e->getMessage()}\n";
    echo "Trace: {$e->getTraceAsString()}\n";
}

// Limpiar archivo temporal
if (file_exists($signaturePath)) {
    unlink($signaturePath);
}

echo "\n=== Fin de la Prueba ===\n";