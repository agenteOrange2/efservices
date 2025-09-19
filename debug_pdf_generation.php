<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\Log;

// Buscar el driver ID 4
$driver = UserDriverDetail::with([
    'user',
    'application.details',
    'application.ownerOperatorDetail',
    'application.thirdPartyDetail'
])->find(4);

if (!$driver) {
    echo "Driver ID 4 no encontrado\n";
    exit(1);
}

echo "=== DEBUG PDF GENERATION PARA DRIVER ID 4 ===\n";
echo "Driver ID: " . $driver->id . "\n";
echo "User ID: " . $driver->user_id . "\n";
echo "Nombre: " . ($driver->user ? $driver->user->name : 'N/A') . "\n";

if ($driver->application) {
    echo "\n=== DATOS DE APLICACIÓN ===\n";
    echo "Application ID: " . $driver->application->id . "\n";
    
    if ($driver->application->details) {
        echo "\n=== DETALLES DE APLICACIÓN ===\n";
        echo "applying_position: " . ($driver->application->details->applying_position ?? 'NULL') . "\n";
        echo "owner_name: " . ($driver->application->details->owner_name ?? 'NULL') . "\n";
        echo "hire_date: " . ($driver->application->details->hire_date ?? 'NULL') . "\n";
        
        // Verificar qué tipo de documento debería generarse
        $applyingPosition = $driver->application->details->applying_position;
        echo "\n=== LÓGICA DE GENERACIÓN DE DOCUMENTOS ===\n";
        echo "applying_position obtenido: '" . $applyingPosition . "'\n";
        
        if ($applyingPosition === 'owner_operator') {
            echo "✓ DEBERÍA generar documento de OWNER OPERATOR\n";
            
            // Verificar si existen los datos de owner operator
            if ($driver->application->ownerOperatorDetail) {
                echo "✓ Datos de owner operator encontrados:\n";
                echo "  - business_name: " . ($driver->application->ownerOperatorDetail->business_name ?? 'NULL') . "\n";
                echo "  - tax_id: " . ($driver->application->ownerOperatorDetail->tax_id ?? 'NULL') . "\n";
                echo "  - address: " . ($driver->application->ownerOperatorDetail->address ?? 'NULL') . "\n";
            } else {
                echo "✗ NO se encontraron datos de owner operator\n";
            }
        } elseif ($applyingPosition === 'third_party_driver') {
            echo "✓ DEBERÍA generar documento de THIRD PARTY DRIVER\n";
            
            // Verificar si existen los datos de third party driver
            if ($driver->application->thirdPartyDetail) {
                echo "✓ Datos de third party driver encontrados:\n";
                echo "  - emergency_contact_name: " . ($driver->application->thirdPartyDetail->emergency_contact_name ?? 'NULL') . "\n";
                echo "  - emergency_contact_phone: " . ($driver->application->thirdPartyDetail->emergency_contact_phone ?? 'NULL') . "\n";
            } else {
                echo "✗ NO se encontraron datos de third party driver\n";
            }
        } else {
            echo "✗ applying_position no reconocido: '" . $applyingPosition . "'\n";
        }
        
    } else {
        echo "✗ NO se encontraron detalles de aplicación\n";
    }
} else {
    echo "✗ NO se encontró aplicación\n";
}

// Verificar qué documentos existen actualmente
echo "\n=== VERIFICACIÓN DE ARCHIVOS EXISTENTES ===\n";
$driverPath = storage_path('app/public/driver/' . $driver->id);
echo "Directorio del driver: " . $driverPath . "\n";

if (is_dir($driverPath)) {
    echo "✓ Directorio del driver existe\n";
    
    // Buscar archivos PDF
    $files = glob($driverPath . '/**/*.pdf');
    if (!empty($files)) {
        echo "Archivos PDF encontrados:\n";
        foreach ($files as $file) {
            $relativePath = str_replace($driverPath . '/', '', $file);
            $size = filesize($file);
            $modified = date('Y-m-d H:i:s', filemtime($file));
            echo "  - " . $relativePath . " (" . number_format($size) . " bytes, modificado: " . $modified . ")\n";
        }
    } else {
        echo "✗ No se encontraron archivos PDF\n";
    }
} else {
    echo "✗ Directorio del driver no existe\n";
}

echo "\n=== FIN DEBUG ===\n";