<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserDriverDetail;
use App\Models\DriverApplication;
use App\Models\DriverApplicationDetail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

echo "=== DEBUG: Rastreando applying_position para Driver ID 4 ===\n\n";

// 1. Verificar estado actual en la base de datos
$driver = UserDriverDetail::with(['application.details'])->find(4);

if (!$driver) {
    echo "ERROR: Driver ID 4 no encontrado\n";
    exit(1);
}

echo "1. Estado actual del driver:\n";
echo "   - Driver ID: {$driver->id}\n";
echo "   - Nombre: {$driver->user->name}\n";
echo "   - Application ID: " . ($driver->application ? $driver->application->id : 'NULL') . "\n";

if ($driver->application && $driver->application->details) {
    echo "   - applying_position: {$driver->application->details->applying_position}\n";
} else {
    echo "   - applying_position: NO DISPONIBLE (falta application o details)\n";
}

echo "\n";

// 2. Verificar directamente en la tabla
echo "2. Verificación directa en base de datos:\n";
$directQuery = DB::table('driver_application_details')
    ->join('driver_applications', 'driver_application_details.driver_application_id', '=', 'driver_applications.id')
    ->where('driver_applications.user_driver_detail_id', 4)
    ->select('driver_application_details.applying_position', 'driver_application_details.updated_at')
    ->first();

if ($directQuery) {
    echo "   - applying_position (directo): {$directQuery->applying_position}\n";
    echo "   - Última actualización: {$directQuery->updated_at}\n";
} else {
    echo "   - No se encontraron detalles de aplicación\n";
}

echo "\n";

// 3. Cambiar temporalmente a owner_operator y verificar
echo "3. Cambiando temporalmente a 'owner_operator'...\n";

if ($driver->application && $driver->application->details) {
    $originalValue = $driver->application->details->applying_position;
    
    // Cambiar el valor
    $driver->application->details->update(['applying_position' => 'owner_operator']);
    
    echo "   - Valor original: {$originalValue}\n";
    echo "   - Nuevo valor guardado\n";
    
    // Verificar que se guardó correctamente
    $driver->refresh();
    $newValue = $driver->application->details->applying_position;
    echo "   - Valor después del refresh: {$newValue}\n";
    
    // Simular el proceso de generación de PDFs
    echo "\n4. Simulando proceso de generación de PDFs...\n";
    
    // Cargar como lo hace CertificationStep
    $userDriverDetail = UserDriverDetail::with([
        'application.details', 
        'application.ownerOperatorDetail', 
        'user',
        'carrier'
    ])->find(4);
    
    $application = $userDriverDetail->application;
    
    if ($application && $application->details) {
        $applyingPosition = $application->details->applying_position ?? 'unknown';
        echo "   - applying_position en simulación: {$applyingPosition}\n";
        
        if ($applyingPosition === 'owner_operator') {
            echo "   - ✓ Se debería generar contrato de propietario-operador\n";
        } else {
            echo "   - ✗ NO se generaría contrato de propietario-operador\n";
        }
    }
    
    echo "\n5. Restaurando valor original...\n";
    $driver->application->details->update(['applying_position' => $originalValue]);
    echo "   - Valor restaurado a: {$originalValue}\n";
    
} else {
    echo "   - ERROR: No se puede cambiar el valor, faltan datos de aplicación\n";
}

echo "\n=== FIN DEL DEBUG ===\n";