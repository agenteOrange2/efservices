<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Driver\DriverApplicationDetail;
use App\Models\UserDriverDetail;
use App\Models\OwnerOperatorDetail;
use App\Models\ThirdPartyDetail;
use App\Models\Admin\Vehicle\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

try {
    DB::beginTransaction();
    
    echo "Creando datos de ejemplo para driver types...\n";
    
    // Crear usuarios de ejemplo si no existen
    $users = [];
    for ($i = 1; $i <= 3; $i++) {
        $user = User::firstOrCreate(
            ['email' => "driver{$i}@example.com"],
            [
                'name' => "Driver {$i}",
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $users[] = $user;
        echo "Usuario creado/encontrado: {$user->name} (ID: {$user->id})\n";
    }
    
    // Obtener vehículos existentes
    $vehicles = Vehicle::take(3)->get();
    if ($vehicles->count() == 0) {
        echo "No hay vehículos disponibles. Creando vehículos de ejemplo...\n";
        for ($i = 1; $i <= 3; $i++) {
            $vehicle = Vehicle::create([
                'company_unit_number' => "UNIT-{$i}",
                'make' => 'Freightliner',
                'model' => 'Cascadia',
                'year' => 2020 + $i,
                'vin' => 'VIN' . str_pad($i, 14, '0', STR_PAD_LEFT),
                'license_plate' => "ABC-{$i}23",
                'status' => 'active'
            ]);
            $vehicles->push($vehicle);
            echo "Vehículo creado: {$vehicle->company_unit_number}\n";
        }
    }
    
    // Crear aplicaciones de conductor
    foreach ($users as $index => $user) {
        // Crear DriverApplication
        $driverApp = DriverApplication::firstOrCreate(
            ['user_id' => $user->id],
            [
                'status' => 'pending',
                'completed_at' => now(),
            ]
        );
        echo "DriverApplication creada/encontrada: ID {$driverApp->id} para usuario {$user->name}\n";
        
        // Crear DriverApplicationDetail
        $vehicle = $vehicles[$index % $vehicles->count()];
        $positions = ['driver', 'owner_operator', 'third_party_driver'];