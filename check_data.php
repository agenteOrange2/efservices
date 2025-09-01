<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Admin\Vehicle\VehicleMaintenance;
use App\Models\Admin\Vehicle\Vehicle;

echo "=== VERIFICACIÓN DE DATOS ===\n";
echo "Total mantenimientos: " . VehicleMaintenance::count() . "\n";
echo "Total vehículos: " . Vehicle::count() . "\n\n";

$maintenance = VehicleMaintenance::with('vehicle')->first();
if ($maintenance) {
    echo "=== PRIMER MANTENIMIENTO ===\n";
    echo "ID: " . $maintenance->id . "\n";
    echo "Service Tasks: " . $maintenance->service_tasks . "\n";
    echo "Cost: " . $maintenance->cost . "\n";
    echo "Service Date: " . $maintenance->service_date . "\n";
    echo "Status: " . ($maintenance->status ? 'Completado' : 'Pendiente') . "\n";
    echo "Vehicle: " . ($maintenance->vehicle ? $maintenance->vehicle->make . ' ' . $maintenance->vehicle->model : 'Sin vehículo') . "\n\n";
} else {
    echo "No hay mantenimientos\n";
}

$vehicle = Vehicle::first();
if ($vehicle) {
    echo "=== PRIMER VEHÍCULO ===\n";
    echo "ID: " . $vehicle->id . "\n";
    echo "Make: " . $vehicle->make . "\n";
    echo "Model: " . $vehicle->model . "\n";
    echo "Year: " . $vehicle->year . "\n\n";
} else {
    echo "No hay vehículos\n";
}