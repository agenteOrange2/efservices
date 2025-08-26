<?php

/**
 * Debug script for analyzing admin access issues
 * This script helps identify why admin pages show generic content
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Carrier;
use App\Models\User;

// Bootstrap Laravel application properly
$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$app->boot();

// Set up the application context
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/', 'GET');
$response = $kernel->handle($request);

// Initialize facades
Illuminate\Support\Facades\Facade::setFacadeApplication($app);

echo "=== DEBUG ADMIN ACCESS ===\n\n";

try {
    // 1. Verificar conexión a la base de datos
    echo "1. VERIFICANDO CONEXIÓN A BASE DE DATOS:\n";
    try {
        DB::connection()->getPdo();
        echo "   ✓ Conexión a BD exitosa\n";
        echo "   - Driver: " . DB::connection()->getDriverName() . "\n";
        echo "   - Database: " . DB::connection()->getDatabaseName() . "\n";
    } catch (Exception $e) {
        echo "   ✗ Error de conexión: " . $e->getMessage() . "\n";
        exit(1);
    }
    
    // 2. Verificar existencia del carrier 'depeche-mode-llc'
    echo "\n2. VERIFICANDO CARRIER 'depeche-mode-llc':\n";
    try {
        $carrier = Carrier::where('slug', 'depeche-mode-llc')->first();
        if ($carrier) {
            echo "   ✓ Carrier encontrado\n";
            echo "   - ID: {$carrier->id}\n";
            echo "   - Nombre: {$carrier->name}\n";
            echo "   - Slug: {$carrier->slug}\n";
            echo "   - Estado: " . ($carrier->status ? 'Activo' : 'Inactivo') . "\n";
            echo "   - Creado: {$carrier->created_at}\n";
        } else {
            echo "   ✗ Carrier 'depeche-mode-llc' NO encontrado\n";
            echo "   - Carriers disponibles:\n";
            $carriers = Carrier::select('id', 'name', 'slug')->limit(10)->get();
            foreach ($carriers as $c) {
                echo "     * {$c->slug} ({$c->name})\n";
            }
        }
    } catch (Exception $e) {
        echo "   ✗ Error al buscar carrier: " . $e->getMessage() . "\n";
    }
    
    // 3. Verificar rutas admin registradas
    echo "\n3. VERIFICANDO RUTAS ADMIN:\n";
    try {
        $adminRoutes = [];
        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();
            if (strpos($uri, 'admin/carrier') === 0) {
                $adminRoutes[] = [
                    'uri' => $uri,
                    'methods' => implode('|', $route->methods()),
                    'name' => $route->getName(),
                    'action' => $route->getActionName(),
                    'middleware' => implode(', ', $route->middleware())
                ];
            }
        }
        
        if (!empty($adminRoutes)) {
            echo "   ✓ Rutas admin encontradas: " . count($adminRoutes) . "\n";
            foreach ($adminRoutes as $route) {
                echo "   - {$route['methods']} {$route['uri']}\n";
                echo "     * Nombre: {$route['name']}\n";
                echo "     * Acción: {$route['action']}\n";
                echo "     * Middleware: {$route['middleware']}\n\n";
            }
        } else {
            echo "   ✗ No se encontraron rutas admin/carrier\n";
        }
    } catch (Exception $e) {
        echo "   ✗ Error al verificar rutas: " . $e->getMessage() . "\n";
    }
    
    // 4. Verificar middleware de autenticación
    echo "\n4. VERIFICANDO MIDDLEWARE:\n";
    try {
        $middlewareAliases = $app['router']->getMiddleware();
        echo "   - Middleware registrados:\n";
        foreach (['auth', 'web', 'check.user.status', 'security.headers'] as $middleware) {
            if (isset($middlewareAliases[$middleware])) {
                echo "     ✓ {$middleware}: {$middlewareAliases[$middleware]}\n";
            } else {
                echo "     ✗ {$middleware}: NO REGISTRADO\n";
            }
        }
    } catch (Exception $e) {
        echo "   ✗ Error al verificar middleware: " . $e->getMessage() . "\n";
    }
    
    // 5. Simular request a las rutas admin
    echo "\n5. SIMULANDO REQUESTS A RUTAS ADMIN:\n";
    $testRoutes = [
        'admin/carrier/depeche-mode-llc',
        'admin/carrier/depeche-mode-llc/user-carriers',
        'admin/carrier/depeche-mode-llc/drivers',
        'admin/carrier/depeche-mode-llc/documents'
    ];
    
    foreach ($testRoutes as $testRoute) {
        echo "   Probando: /{$testRoute}\n";
        try {
            $request = Request::create('/' . $testRoute, 'GET');
            $route = Route::getRoutes()->match($request);
            
            if ($route) {
                echo "     ✓ Ruta encontrada\n";
                echo "     - Controlador: {$route->getActionName()}\n";
                echo "     - Middleware: " . implode(', ', $route->middleware()) . "\n";
                
                // Verificar si el controlador existe
                $action = $route->getAction();
                if (isset($action['controller'])) {
                    $controllerClass = explode('@', $action['controller'])[0];
                    if (class_exists($controllerClass)) {
                        echo "     ✓ Controlador existe: {$controllerClass}\n";
                    } else {
                        echo "     ✗ Controlador NO existe: {$controllerClass}\n";
                    }
                }
            } else {
                echo "     ✗ Ruta NO encontrada\n";
            }
        } catch (Exception $e) {
            echo "     ✗ Error: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
    
    // 6. Verificar usuarios admin
    echo "\n6. VERIFICANDO USUARIOS ADMIN:\n";
    try {
        $adminUsers = User::whereHas('roles', function($query) {
            $query->where('name', 'admin');
        })->get(['id', 'name', 'email', 'created_at']);
        
        if ($adminUsers->count() > 0) {
            echo "   ✓ Usuarios admin encontrados: {$adminUsers->count()}\n";
            foreach ($adminUsers as $user) {
                echo "     - {$user->name} ({$user->email})\n";
            }
        } else {
            echo "   ✗ No se encontraron usuarios admin\n";
            echo "   - Total usuarios: " . User::count() . "\n";
        }
    } catch (Exception $e) {
        echo "   ✗ Error al verificar usuarios: " . $e->getMessage() . "\n";
    }
    
    // 7. Verificar archivos de vista
    echo "\n7. VERIFICANDO ARCHIVOS DE VISTA:\n";
    $viewPaths = [
        'resources/views/admin/carriers/edit.blade.php',
        'resources/views/admin/carriers/show.blade.php',
        'resources/views/admin/user-carriers/index.blade.php',
        'resources/views/admin/drivers/index.blade.php',
        'resources/views/admin/carrier-documents/index.blade.php',
        'resources/views/welcome.blade.php'
    ];
    
    foreach ($viewPaths as $viewPath) {
        $fullPath = __DIR__ . '/' . $viewPath;
        if (file_exists($fullPath)) {
            echo "   ✓ {$viewPath}\n";
        } else {
            echo "   ✗ {$viewPath} NO EXISTE\n";
        }
    }
    
    // 8. Verificar configuración de sesiones
    echo "\n8. VERIFICANDO CONFIGURACIÓN DE SESIONES:\n";
    echo "   - Session driver: " . config('session.driver') . "\n";
    echo "   - Session lifetime: " . config('session.lifetime') . " minutos\n";
    echo "   - Session path: " . config('session.path') . "\n";
    echo "   - Session domain: " . config('session.domain') . "\n";
    
    echo "\n=== FIN DEL ANÁLISIS ===\n";
    echo "\nRECOMENDACIONES:\n";
    echo "1. Verificar que el usuario esté autenticado como admin\n";
    echo "2. Comprobar que el carrier 'depeche-mode-llc' existe en la BD\n";
    echo "3. Revisar que las rutas admin estén correctamente protegidas\n";
    echo "4. Verificar que los controladores admin existan y funcionen\n";
    echo "5. Comprobar la configuración de middleware de autenticación\n";
    
} catch (Exception $e) {
    echo "ERROR GENERAL: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}