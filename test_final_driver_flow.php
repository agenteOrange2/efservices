<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Http\Middleware\CheckUserStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

echo "=== TESTING COMPLETE DRIVER FLOW ===" . PHP_EOL . PHP_EOL;

// Encontrar el usuario driver de prueba
$user = User::find(49);

if (!$user) {
    echo "❌ User not found!" . PHP_EOL;
    exit(1);
}

echo "✅ User found: " . $user->email . PHP_EOL;
echo "User status: " . $user->status . PHP_EOL;
echo "User roles: " . implode(', ', $user->getRoleNames()->toArray()) . PHP_EOL;

if ($user->driverDetails) {
    echo "Driver Details Status: " . $user->driverDetails->status . PHP_EOL;
    echo "Current Step: " . ($user->driverDetails->current_step ?? 'null') . PHP_EOL;
    echo "Application Completed: " . ($user->driverDetails->application_completed ? 'Yes' : 'No') . PHP_EOL;
}

if ($user->driverApplication) {
    echo "Driver Application Status: " . $user->driverApplication->status . PHP_EOL;
}

echo PHP_EOL . "=== TESTING MIDDLEWARE LOGIC ===" . PHP_EOL;

// Simular autenticación
Auth::login($user);

// Crear request simulado para dashboard
$request = Request::create('/driver/dashboard', 'GET');
$request->setUserResolver(function () use ($user) {
    return $user;
});

echo "Testing access to: " . $request->path() . PHP_EOL;

// Instanciar middleware
$middleware = new CheckUserStatus();

try {
    $response = $middleware->handle($request, function ($req) {
        return response('Dashboard Access Granted', 200);
    });
    
    if ($response->getStatusCode() === 302) {
        echo "✅ BLOCKED: Redirected to " . $response->getTargetUrl() . PHP_EOL;
        
        // Analizar a dónde debería redirigir según el estado
        if ($user->status != 1) {
            echo "Expected: Login (user inactive)" . PHP_EOL;
        } elseif (!$user->driverDetails) {
            echo "Expected: Complete registration" . PHP_EOL;
        } elseif ($user->driverDetails->status != 1) {
            echo "Expected: Driver pending" . PHP_EOL;
        } elseif (!$user->driverDetails->application_completed) {
            echo "Expected: Registration step " . ($user->driverDetails->current_step ?? 1) . PHP_EOL;
        } elseif ($user->driverApplication && $user->driverApplication->status === 'draft') {
            echo "Expected: Registration step (draft status)" . PHP_EOL;
        }
        
    } else {
        echo "❌ FAILED: Dashboard access was granted when it should be blocked" . PHP_EOL;
        echo "Response: " . $response->getContent() . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== CONCLUSION ===" . PHP_EOL;
echo "The middleware should block access to /driver/dashboard for this user" . PHP_EOL;
echo "because the user has incomplete registration (status 2, application not completed, draft status)" . PHP_EOL;