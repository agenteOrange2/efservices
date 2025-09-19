<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Admin\Driver\DriverApplicationDetail;

echo "=== VERIFICANDO TABLA DRIVER_APPLICATION_DETAILS ===\n";

try {
    $columns = DB::select("DESCRIBE driver_application_details");
    echo "Columnas de la tabla driver_application_details:\n";
    foreach ($columns as $column) {
        echo "- {$column->Field} ({$column->Type}) - Null: {$column->Null} - Default: {$column->Default}\n";
    }
} catch (Exception $e) {
    echo "Error al obtener estructura de tabla: {$e->getMessage()}\n";
}

// Verificar registros existentes
echo "\n=== REGISTROS EXISTENTES ===\n";
try {
    $details = DriverApplicationDetail::all();
    foreach ($details as $detail) {
        echo "ID: {$detail->id}, Driver App ID: {$detail->driver_application_id}";
        if (isset($detail->applying_position)) {
            echo ", Applying Position: [{$detail->applying_position}]";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "Error al obtener registros: {$e->getMessage()}\n";
}

echo "\n=== VERIFICACIÓN COMPLETADA ===\n";