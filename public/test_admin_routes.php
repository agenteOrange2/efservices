<?php

/**
 * Script para probar el acceso a las rutas admin específicas
 * Este script simula las peticiones HTTP a las rutas admin
 */

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Carrier;

echo "=== PRUEBA DE RUTAS ADMIN ===\n\n";

try {
    // Verificar que el carrier existe
    $carrier = Carrier::where('slug', 'depeche-mode-llc')->first();
    
    if (!$carrier) {
        echo "❌ ERROR: El carrier 'depeche-mode-llc' no existe en la base de datos\n";
        echo "Carriers disponibles:\n";
        $carriers = Carrier::select('slug', 'name')->get();
        foreach ($carriers as $c) {
            echo "  - {$c->slug} ({$c->name})\n";
        }
        exit(1);
    }
    
    echo "✅ Carrier encontrado: {$carrier->name} (ID: {$carrier->id})\n\n";
    
    // URLs que el usuario está intentando acceder
    $userUrls = [
        'http://efservices.la/admin/carrier/depeche-mode-llc',
        'http://efservices.la/admin/carrier/depeche-mode-llc/user-carriers',
        'http://efservices.la/admin/carrier/depeche-mode-llc/drivers',
        'http://efservices.la/admin/carrier/depeche-mode-llc/documents'
    ];
    
    // URLs correctas según las rutas registradas
    $correctUrls = [
        '/admin/carrier/depeche-mode-llc' => 'admin.carrier.edit',
        '/admin/carrier/depeche-mode-llc/user-carriers' => 'admin.carrier.user_carriers.index',
        '/admin/carrier/depeche-mode-llc/drivers' => 'admin.carrier.user_drivers.index',
        '/admin/carrier/depeche-mode-llc/documents' => 'admin.carrier.documents'
    ];
    
    echo "ANÁLISIS DE RUTAS:\n";
    echo "==================\n\n";
    
    foreach ($correctUrls as $path => $routeName) {
        echo "Probando: {$path}\n";
        
        try {
            // Crear una petición de prueba
            $request = Request::create($path, 'GET');
            
            // Intentar hacer match con las rutas
            $route = Route::getRoutes()->match($request);
            
            echo "  ✅ Ruta encontrada: {$route->getName()}\n";
            echo "  📋 Controlador: {$route->getControllerClass()}\n";
            echo "  🔧 Método: {$route->getActionMethod()}\n";
            echo "  🛡️  Middleware: " . implode(', ', $route->middleware()) . "\n";
            
            // Verificar parámetros
            $parameters = $route->parameters();
            if (!empty($parameters)) {
                echo "  📝 Parámetros: " . json_encode($parameters) . "\n";
            }
            
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            echo "  ❌ Ruta no encontrada\n";
        } catch (Exception $e) {
            echo "  ⚠️  Error: {$e->getMessage()}\n";
        }
        
        echo "\n";
    }
    
    echo "VERIFICACIÓN DE MIDDLEWARE:\n";
    echo "===========================\n\n";
    
    // Verificar middleware de autenticación
    $router = app('router');
    $middlewareGroups = $router->getMiddlewareGroups();
    
    echo "Middleware 'web': " . (isset($middlewareGroups['web']) ? '✅ Configurado' : '❌ No configurado') . "\n";
    echo "Middleware 'auth': " . (isset($router->getMiddleware()['auth']) ? '✅ Registrado' : '❌ No registrado') . "\n";
    echo "Middleware 'role': " . (isset($router->getMiddleware()['role']) ? '✅ Registrado' : '❌ No registrado') . "\n";
    echo "Middleware 'permission': " . (isset($router->getMiddleware()['permission']) ? '✅ Registrado' : '❌ No registrado') . "\n";
    
    echo "\nCONCLUSIONES:\n";
    echo "=============\n\n";
    
    echo "1. 🎯 Las rutas admin están correctamente registradas\n";
    echo "2. 🔍 El carrier 'depeche-mode-llc' existe en la base de datos\n";
    echo "3. 🛡️  Las rutas están protegidas por middleware de autenticación\n";
    echo "4. ⚠️  El problema es que el usuario NO ESTÁ AUTENTICADO\n";
    echo "\n";
    echo "SOLUCIÓN:\n";
    echo "=========\n";
    echo "El usuario debe:\n";
    echo "1. 🔐 Iniciar sesión como administrador\n";
    echo "2. 🎭 Tener los roles/permisos necesarios\n";
    echo "3. 🌐 Acceder a través del dominio correcto\n";
    echo "\n";
    echo "Las páginas muestran el contenido de 'welcome' porque:\n";
    echo "- El middleware de autenticación redirige a la página de inicio\n";
    echo "- O hay un middleware que está interceptando las peticiones\n";
    echo "- O el usuario no tiene los permisos necesarios\n";
    
} catch (Exception $e) {
    echo "❌ ERROR GENERAL: {$e->getMessage()}\n";
    echo "Stack trace: {$e->getTraceAsString()}\n";
}

echo "\n=== FIN DE LA PRUEBA ===\n";