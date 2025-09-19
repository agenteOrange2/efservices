<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Admin\Driver\DriverApplicationDetail;
use Illuminate\Support\Facades\DB;

echo "=== VERIFICANDO DETAIL ID 3 ===\n";

// Buscar por driver_application_id = 3
$detail = DriverApplicationDetail::where('driver_application_id', 3)->first();

if ($detail) {
    echo "Detail encontrado:\n";
    echo "- ID: {$detail->id}\n";
    echo "- Driver Application ID: {$detail->driver_application_id}\n";
    echo "- Applying Position: [{$detail->applying_position}]\n";
    echo "- Applying Position Other: [{$detail->applying_position_other}]\n";
    echo "- Applying Location: [{$detail->applying_location}]\n";
    
    // Verificar datos raw
    echo "\nDatos completos:\n";
    $data = $detail->toArray();
    foreach ($data as $key => $value) {
        echo "- {$key}: [{$value}]\n";
    }
} else {
    echo "No se encontró detail para driver_application_id = 3\n";
    
    // Verificar qué details existen
    echo "\nDetails existentes:\n";
    $allDetails = DriverApplicationDetail::all();
    foreach ($allDetails as $d) {
        echo "- Detail ID: {$d->id}, App ID: {$d->driver_application_id}, Position: [{$d->applying_position}]\n";
    }
}

echo "\n=== VERIFICACIÓN COMPLETADA ===\n";