<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== TESTING ADMIN ROUTES ===\n\n";

// Test routes without authentication
$testRoutes = [
    '/admin/carrier/depeche-mode-llc',
    '/admin/carrier/depeche-mode-llc/user-carriers',
    '/admin/carrier/depeche-mode-llc/drivers',
    '/admin/carrier/depeche-mode-llc/documents'
];

foreach ($testRoutes as $route) {
    echo "Testing route: {$route}\n";
    
    try {
        $request = Illuminate\Http\Request::create($route, 'GET');
        $response = $kernel->handle($request);
        
        echo "   Status: {$response->getStatusCode()}\n";
        
        if ($response->getStatusCode() === 302) {
            $location = $response->headers->get('Location');
            echo "   Redirect to: {$location}\n";
        }
        
        $content = $response->getContent();
        if (strpos($content, 'welcome') !== false) {
            echo "   ❌ Shows welcome page (not authenticated)\n";
        } elseif (strpos($content, 'login') !== false) {
            echo "   ❌ Shows login page (authentication required)\n";
        } elseif (strpos($content, 'carrier') !== false || strpos($content, 'admin') !== false) {
            echo "   ✅ Shows admin content\n";
        } else {
            echo "   ⚠️  Unknown content\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "=== AUTHENTICATION CHECK ===\n";

// Check if there are any admin users
try {
    $adminUsers = App\Models\User::whereHas('roles', function($query) {
        $query->where('name', 'admin');
    })->get(['id', 'name', 'email']);
    
    echo "Admin users found: " . $adminUsers->count() . "\n";
    foreach ($adminUsers as $user) {
        echo "   - {$user->name} ({$user->email})\n";
    }
    
} catch (Exception $e) {
    echo "Error checking admin users: " . $e->getMessage() . "\n";
}

echo "\n=== MIDDLEWARE STACK CHECK ===\n";

// Check middleware for admin routes
$router = app('router');
$routes = $router->getRoutes();

foreach ($routes as $route) {
    if (strpos($route->uri(), 'admin/carrier/{carrier}') === 0) {
        echo "Route: {$route->uri()}\n";
        echo "   Name: {$route->getName()}\n";
        echo "   Middleware: " . implode(', ', $route->gatherMiddleware()) . "\n";
        echo "   Action: {$route->getActionName()}\n\n";
        break; // Just show one example
    }
}

echo "=== SUMMARY ===\n";
echo "If routes redirect to login, the issue is authentication.\n";
echo "If routes show welcome page, there might be a catch-all route issue.\n";
echo "Check the results above to identify the specific problem.\n";