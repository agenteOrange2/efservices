<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "=== VERIFICACIÓN DEL ESTADO DEL USUARIO ===\n\n";

// Buscar el usuario
$user = User::where('email', 'checo@test.com')->first();

if ($user) {
    echo "Usuario encontrado:\n";
    echo "- ID: {$user->id}\n";
    echo "- Email: {$user->email}\n";
    echo "- Status actual: {$user->status}\n";
    echo "- Creado: {$user->created_at}\n";
    echo "- Actualizado: {$user->updated_at}\n\n";
    
    // Cambiar temporalmente el status a 2 para probar el middleware
    echo "Cambiando status a 2 (Pending) para probar el middleware...\n";
    $user->status = 2;
    $user->save();
    
    echo "✓ Status cambiado a: {$user->status}\n";
    echo "\nAhora el usuario checo@test.com tiene status=2 (Pending)\n";
    echo "Con el middleware aplicado, NO debería poder acceder al dashboard.\n\n";
    
    echo "PARA PROBAR:\n";
    echo "1. Acceder a http://localhost:8000/dashboard como checo@test.com\n";
    echo "2. El usuario DEBERÍA ser redirigido/logout automáticamente\n";
    echo "3. Si funciona correctamente, el middleware está bloqueando el acceso\n\n";
    
    echo "PARA RESTAURAR EL ACCESO (después de la prueba):\n";
    echo "- Ejecutar: php restore_user_access.php\n";
    echo "- O manualmente cambiar status a 1 en la base de datos\n";
    
} else {
    echo "❌ Usuario checo@test.com no encontrado\n";
}

echo "\n=== FIN ===\n";