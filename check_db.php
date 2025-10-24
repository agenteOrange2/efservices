<?php
require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => '127.0.0.1',
    'port'      => 3309,
    'database'  => 'efservices_db',
    'username'  => 'root',
    'password'  => '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    echo "=== ESTRUCTURA DE LA TABLA driver_application_details ===\n";
    $structure = $capsule::select("DESCRIBE driver_application_details");
    foreach ($structure as $column) {
        echo "Campo: {$column->Field}, Tipo: {$column->Type}, Null: {$column->Null}, Default: {$column->Default}\n";
    }
    
    echo "\n=== ÚLTIMOS 10 REGISTROS ===\n";
    $records = $capsule::select("SELECT * FROM driver_application_details ORDER BY id DESC LIMIT 10");
    foreach ($records as $record) {
        echo "ID: {$record->id}, driver_application_id: {$record->driver_application_id}, applying_position: {$record->applying_position}\n";
    }
    
    echo "\n=== REGISTRO ESPECÍFICO ID=22 ===\n";
    $specific = $capsule::select("SELECT * FROM driver_application_details WHERE id = 22");
    if (!empty($specific)) {
        $record = $specific[0];
        echo "ID: {$record->id}\n";
        echo "driver_application_id: {$record->driver_application_id}\n";
        echo "applying_position: '{$record->applying_position}'\n";
        echo "applying_position_other: '{$record->applying_position_other}'\n";
        echo "created_at: {$record->created_at}\n";
        echo "updated_at: {$record->updated_at}\n";
    } else {
        echo "No se encontró el registro con ID=22\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}