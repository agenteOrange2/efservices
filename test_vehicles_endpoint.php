<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Test the vehicles-by-carrier endpoint
echo "Testing vehicles-by-carrier endpoint...\n";
echo "==========================================\n\n";

try {
    // Create a test request
    $request = Illuminate\Http\Request::create('/admin/vehicles/emergency-repairs/vehicles-by-carrier/1', 'GET');
    
    // Process the request
    $response = $kernel->handle($request);
    
    echo "Status Code: " . $response->getStatusCode() . "\n";
    echo "Content Type: " . $response->headers->get('Content-Type') . "\n";
    echo "Response Content:\n";
    echo $response->getContent() . "\n\n";
    
    // Test with carrier ID 2
    echo "Testing with carrier ID 2...\n";
    $request2 = Illuminate\Http\Request::create('/admin/vehicles/emergency-repairs/vehicles-by-carrier/2', 'GET');
    $response2 = $kernel->handle($request2);
    
    echo "Status Code: " . $response2->getStatusCode() . "\n";
    echo "Response Content:\n";
    echo $response2->getContent() . "\n\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

$kernel->terminate($request, $response);