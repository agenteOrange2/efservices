<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "=== RESTAURAR ACCESO DEL USUARIO ===\n\n";

// Buscar el usuario
$user = User::where('email', 'checo@test.com')->first();

if ($user) {
    echo "Usuario encontrado:\n";
    echo "- Email: {$user->email}\n";
    echo "- Status actual: {$user->status}\n\n";
    
    // Cambiar el status a 1 (Active)
    echo "Restaurando acceso (cambiando status a 1 - Active)...\n";
    $user->status = 1;
    $user->save();
    
    echo "✓ Status cambiado a: {$user->status}\n";
    echo "\n✅ El usuario checo@test.com ahora puede acceder al dashboard nuevamente.\n";
    
} else {
    echo "❌ Usuario checo@test.com no encontrado\n";
}

echo "\n=== FIN ===\n";