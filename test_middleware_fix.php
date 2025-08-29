<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Models\User;

echo "=== PRUEBA DE MIDDLEWARE 'check.user.status' EN RUTAS DE DRIVER ===\n\n";

// 1. Verificar el estado actual del usuario checo@test.com
echo "1. VERIFICANDO ESTADO DEL USUARIO checo@test.com:\n";
$user = User::where('email', 'checo@test.com')->first();

if ($user) {
    echo "   - ID: {$user->id}\n";
    echo "   - Email: {$user->email}\n";
    echo "   - Status: {$user->status}\n";
    echo "   - Status esperado para acceso: 1 (Active)\n";
    echo "   - ¿Debería tener acceso?: " . ($user->status == 1 ? 'SÍ' : 'NO') . "\n\n";
} else {
    echo "   - Usuario no encontrado\n\n";
}

// 2. Verificar las rutas que ahora tienen el middleware aplicado
echo "2. VERIFICANDO RUTAS CON MIDDLEWARE 'check.user.status':\n";

$routesWithMiddleware = [];
foreach (Route::getRoutes() as $route) {
    $middleware = $route->gatherMiddleware();
    if (in_array('check.user.status', $middleware)) {
        $routesWithMiddleware[] = [
            'uri' => $route->uri(),
            'name' => $route->getName(),
            'methods' => implode('|', $route->methods())
        ];
    }
}

if (!empty($routesWithMiddleware)) {
    echo "   Rutas protegidas encontradas (" . count($routesWithMiddleware) . "):\n";
    foreach ($routesWithMiddleware as $route) {
        echo "   - [{$route['methods']}] {$route['uri']}";
        if ($route['name']) {
            echo " (nombre: {$route['name']})";
        }
        echo "\n";
    }
} else {
    echo "   ⚠️  NO se encontraron rutas con middleware 'check.user.status'\n";
}

echo "\n";

// 3. Verificar específicamente las rutas de driver con nombres correctos
echo "3. VERIFICANDO RUTAS ESPECÍFICAS DE DRIVER:\n";

$driverRoutes = [
    'driver.dashboard',
    'driver.driver.trainings.index',
    'driver.pending',
    'driver.rejected',
    'driver.documents.pending',
    'driver.registration.continue',
    'driver.select_carrier',
    'driver.carrier.status'
];

foreach ($driverRoutes as $routeName) {
    $route = Route::getRoutes()->getByName($routeName);
    if ($route) {
        $middleware = $route->gatherMiddleware();
        $hasCheckUserStatus = in_array('check.user.status', $middleware);
        $hasAuth = in_array('auth', $middleware);
        
        echo "   - {$routeName}:\n";
        echo "     * URI: {$route->uri()}\n";
        echo "     * Auth: " . ($hasAuth ? '✓' : '✗') . "\n";
        echo "     * check.user.status: " . ($hasCheckUserStatus ? '✓' : '✗') . "\n";
        echo "     * Middleware completo: " . implode(', ', $middleware) . "\n\n";
    } else {
        echo "   - {$routeName}: ⚠️  Ruta no encontrada\n\n";
    }
}

// 4. Verificar rutas sin prefijo 'driver.'
echo "4. VERIFICANDO RUTAS SIN PREFIJO 'driver.':\n";

$simpleRoutes = [
    'dashboard',
    'pending',
    'rejected',
    'documents.pending',
    'registration.continue',
    'select_carrier',
    'carrier.status'
];

foreach ($simpleRoutes as $routeName) {
    $route = Route::getRoutes()->getByName($routeName);
    if ($route) {
        $middleware = $route->gatherMiddleware();
        $hasCheckUserStatus = in_array('check.user.status', $middleware);
        $hasAuth = in_array('auth', $middleware);
        
        echo "   - {$routeName}:\n";
        echo "     * URI: {$route->uri()}\n";
        echo "     * Auth: " . ($hasAuth ? '✓' : '✗') . "\n";
        echo "     * check.user.status: " . ($hasCheckUserStatus ? '✓' : '✗') . "\n";
        echo "     * Middleware completo: " . implode(', ', $middleware) . "\n\n";
    } else {
        echo "   - {$routeName}: ⚠️  Ruta no encontrada\n\n";
    }
}

// 5. Simulación del comportamiento del middleware
echo "5. SIMULACIÓN DEL COMPORTAMIENTO DEL MIDDLEWARE:\n";

if ($user) {
    echo "   Para el usuario checo@test.com (status: {$user->status}):\n";
    
    if ($user->status != 1) {
        echo "   ✓ El middleware DEBERÍA redirigir/logout al usuario\n";
        echo "   ✓ Razón: user.status ({$user->status}) != 1\n";
        echo "   ✓ Acción esperada: Logout y redirección\n";
    } else {
        echo "   ✓ El middleware DEBERÍA permitir el acceso\n";
        echo "   ✓ Razón: user.status == 1 (Active)\n";
    }
}

echo "\n";

// 6. Verificar la configuración del middleware en bootstrap/app.php
echo "6. VERIFICANDO CONFIGURACIÓN DEL MIDDLEWARE:\n";

$appFile = file_get_contents(__DIR__ . '/bootstrap/app.php');
if (strpos($appFile, "'check.user.status' => \\App\\Http\\Middleware\\CheckUserStatus::class") !== false) {
    echo "   ✓ Middleware 'check.user.status' está registrado en bootstrap/app.php\n";
} else {
    echo "   ✗ Middleware 'check.user.status' NO está registrado en bootstrap/app.php\n";
}

echo "\n";

// 7. Recomendaciones
echo "7. RECOMENDACIONES:\n";

if ($user && $user->status != 1) {
    echo "   📋 PARA PROBAR EL BLOQUEO:\n";
    echo "   1. Acceder a http://localhost:8000/dashboard como checo@test.com\n";
    echo "   2. El usuario DEBERÍA ser redirigido/logout automáticamente\n";
    echo "   3. Si aún puede acceder, verificar que el middleware esté funcionando\n\n";
    
    echo "   📋 PARA PERMITIR ACCESO (si es necesario):\n";
    echo "   - Ejecutar: UPDATE users SET status = 1 WHERE email = 'checo@test.com';\n";
    echo "   - Esto cambiará el status de 2 (Pending) a 1 (Active)\n\n";
}

echo "   📋 VERIFICACIÓN ADICIONAL:\n";
echo "   - Revisar logs de Laravel para ver si el middleware se ejecuta\n";
echo "   - Verificar que no haya cache de rutas: php artisan route:clear\n";
echo "   - Verificar que no haya cache de configuración: php artisan config:clear\n";

echo "\n=== FIN DE LA PRUEBA ===\n";