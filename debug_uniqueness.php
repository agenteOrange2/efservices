<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Simular una request POST
$request = Illuminate\Http\Request::create('/carrier/wizard/check-uniqueness', 'POST', [
    'field' => 'email',
    'value' => 'test@example.com'
]);

$response = $kernel->handle($request);

echo "Status Code: " . $response->getStatusCode() . "\n";
echo "Content Type: " . $response->headers->get('Content-Type') . "\n";
echo "Response Content: " . $response->getContent() . "\n";

$kernel->terminate($request, $response);