<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverApplication;

// Buscar usuario checo@test.com
$user = User::where('email', 'checo@test.com')->first();

if (!$user) {
    echo "Usuario no encontrado\n";
    exit;
}

echo "=== SIMULANDO PROCESO DE APROBACIÓN ===\n";
echo "Estado ANTES de la aprobación:\n";
echo "UserDriverDetail Status: {$user->driverDetails->status}\n";
echo "DriverApplication Status: {$user->driverApplication->status}\n";
echo "\n";

// Simular el proceso de aprobación
echo "Ejecutando proceso de aprobación...\n";

// 1. Actualizar DriverApplication
$user->driverApplication->update([
    'status' => DriverApplication::STATUS_APPROVED,
    'completed_at' => now()
]);

// 2. Actualizar UserDriverDetail
$user->driverDetails->update([
    'status' => UserDriverDetail::STATUS_ACTIVE,
    'completion_percentage' => 100
]);

echo "Proceso completado.\n\n";

// Recargar datos
$user->refresh();
$user->driverDetails->refresh();
$user->driverApplication->refresh();

echo "Estado DESPUÉS de la aprobación:\n";
echo "UserDriverDetail Status: {$user->driverDetails->status} (" . $user->driverDetails->status_name . ")\n";
echo "DriverApplication Status: {$user->driverApplication->status}\n";
echo "Completion Percentage: {$user->driverDetails->completion_percentage}%\n";
echo "\n";

// Verificar qué debería pasar en el middleware ahora
echo "=== VERIFICACIÓN DEL MIDDLEWARE ===\n";
if ($user->status == 1 && 
    $user->driverDetails->status == UserDriverDetail::STATUS_ACTIVE && 
    $user->driverApplication->status == DriverApplication::STATUS_APPROVED) {
    echo "🟢 Ahora el usuario DEBERÍA tener acceso al dashboard\n";
} else {
    echo "🔴 El usuario AÚN NO debería tener acceso al dashboard\n";
    echo "User status: {$user->status}\n";
    echo "Driver status: {$user->driverDetails->status}\n";
    echo "Application status: {$user->driverApplication->status}\n";
}