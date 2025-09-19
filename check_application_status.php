<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Admin\Driver\DriverApplication;
use Illuminate\Support\Facades\DB;

echo "=== VERIFICANDO ESTADO DE APLICACIÓN ===\n";

// Verificar aplicación ID 3
$application = DriverApplication::find(3);
if ($application) {
    echo "Aplicación encontrada:\n";
    echo "ID: {$application->id}\n";
    echo "User ID: {$application->user_id}\n";
    echo "Applying Position: [{$application->applying_position}]\n";
    echo "Created: {$application->created_at}\n";
    echo "Updated: {$application->updated_at}\n";
    
    // Verificar si el campo está realmente vacío o es null
    if (is_null($application->applying_position)) {
        echo "El campo applying_position es NULL\n";
    } elseif (empty($application->applying_position)) {
        echo "El campo applying_position está vacío\n";
    } else {
        echo "El campo applying_position tiene valor: '{$application->applying_position}'\n";
    }
} else {
    echo "No se encontró la aplicación con ID 3\n";
}

// Verificar la estructura de la tabla
echo "\n=== VERIFICANDO ESTRUCTURA DE TABLA ===\n";
try {
    $columns = DB::select("DESCRIBE driver_applications");
    echo "Columnas de la tabla driver_applications:\n";
    foreach ($columns as $column) {
        echo "- {$column->Field} ({$column->Type}) - Null: {$column->Null} - Default: {$column->Default}\n";
    }
} catch (Exception $e) {
    echo "Error al obtener estructura de tabla: {$e->getMessage()}\n";
}

// Verificar todas las aplicaciones
echo "\n=== TODAS LAS APLICACIONES ===\n";
$allApplications = DriverApplication::all();
foreach ($allApplications as $app) {
    echo "ID: {$app->id}, User: {$app->user_id}, Position: [{$app->applying_position}]\n";
}

echo "\n=== VERIFICACIÓN COMPLETADA ===\n";