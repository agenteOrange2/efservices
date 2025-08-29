<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use App\Models\User;

echo "=== CORRECCIÓN DEL ROL DRIVER ===\n\n";

// 1. Buscar el rol 'driver' incorrecto
$driverRole = Role::where('name', 'driver')->first();
if ($driverRole) {
    echo "1. Rol 'driver' encontrado con ID: {$driverRole->id}\n";
    
    // 2. Buscar usuarios con este rol
    $usersWithDriverRole = User::role('driver')->get();
    echo "2. Usuarios con rol 'driver': {$usersWithDriverRole->count()}\n";
    
    foreach ($usersWithDriverRole as $user) {
        echo "   - {$user->email}\n";
    }
    
    // 3. Crear el rol correcto 'user_driver' si no existe
    $userDriverRole = Role::firstOrCreate(['name' => 'user_driver']);
    echo "3. Rol 'user_driver' creado/encontrado con ID: {$userDriverRole->id}\n";
    
    // 4. Copiar permisos del rol 'driver' al rol 'user_driver'
    $permissions = $driverRole->permissions;
    echo "4. Copiando {$permissions->count()} permisos al rol 'user_driver'\n";
    $userDriverRole->givePermissionTo($permissions);
    
    // 5. Reasignar usuarios al rol correcto
    foreach ($usersWithDriverRole as $user) {
        echo "5. Reasignando usuario {$user->email} al rol 'user_driver'\n";
        $user->removeRole('driver');
        $user->assignRole('user_driver');
    }
    
    // 6. Eliminar el rol incorrecto 'driver'
    echo "6. Eliminando rol incorrecto 'driver'\n";
    $driverRole->delete();
    
    echo "\n✅ CORRECCIÓN COMPLETADA EXITOSAMENTE\n";
} else {
    echo "❌ No se encontró el rol 'driver' incorrecto\n";
}

// Verificar estado final
echo "\n=== VERIFICACIÓN FINAL ===\n";
$roles = Role::all(['name']);
echo "Roles existentes:\n";
foreach ($roles as $role) {
    echo "- {$role->name}\n";
}

echo "\n=== FIN DE CORRECCIÓN ===\n";