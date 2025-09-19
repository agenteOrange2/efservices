<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\UserDriverDetail;

echo "=== VERIFICANDO TABLA USER_DRIVER_DETAILS ===\n";

try {
    $columns = DB::select("DESCRIBE user_driver_details");
    echo "Columnas de la tabla user_driver_details:\n";
    foreach ($columns as $column) {
        echo "- {$column->Field} ({$column->Type}) - Null: {$column->Null} - Default: {$column->Default}\n";
    }
} catch (Exception $e) {
    echo "Error al obtener estructura de tabla: {$e->getMessage()}\n";
}

// Verificar registros existentes
echo "\n=== REGISTROS EXISTENTES ===\n";
try {
    $details = UserDriverDetail::all();
    foreach ($details as $detail) {
        echo "ID: {$detail->id}, User ID: {$detail->user_id}";
        // Mostrar todas las columnas disponibles
        $data = $detail->toArray();
        foreach ($data as $key => $value) {
            if ($key !== 'id' && $key !== 'user_id') {
                echo ", {$key}: [{$value}]";
            }
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "Error al obtener registros: {$e->getMessage()}\n";
}

echo "\n=== VERIFICACIÓN COMPLETADA ===\n";