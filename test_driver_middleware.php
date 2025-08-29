<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Http\Middleware\CheckUserStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

echo "Testing Driver Middleware Logic...\n\n";

// Encontrar el usuario driver de prueba
$user = User::find(49); // checo@test.com

if (!$user) {
    echo "User not found!\n";
    exit(1);
}

echo "Testing with user: {$user->email}\n";
echo "User status: {$user->status}\n";

if ($user->driverDetails) {
    echo "Driver Details Status: {$user->driverDetails->status}\n";
    echo "Current Step: " . ($user->driverDetails->current_step ?? 'null') . "\n";
    echo "Application Completed: " . ($user->driverDetails->application_completed ? 'Yes' : 'No') . "\n";
}

if ($user->driverApplication) {
    echo "Driver Application Status: {$user->driverApplication->status}\n";
}

echo "\n--- Testing Middleware Logic ---\n";

// Simular autenticación
Auth::login($user);

// Crear una request simulada al dashboard
$request = Request::create('/driver/dashboard', 'GET');

// Crear el middleware
$middleware = new CheckUserStatus();

echo "Simulating request to /driver/dashboard...\n";

try {
    $response = $middleware->handle($request, function($req) {
        return response('Dashboard accessed successfully');
    });
    
    if ($response instanceof \Illuminate\Http\RedirectResponse) {
        echo "REDIRECT detected to: " . $response->getTargetUrl() . "\n";
        
        // Obtener el mensaje de sesión si existe
        $session = $response->getSession();
        if ($session && $session->has('info')) {
            echo "Message: " . $session->get('info') . "\n";
        }
        if ($session && $session->has('warning')) {
            echo "Warning: " . $session->get('warning') . "\n";
        }
    } else {
        echo "NO REDIRECT - Dashboard access allowed\n";
        echo "Response: " . $response->getContent() . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n--- Test completed ---\n"