<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Admin\DriverTypeController;
use Illuminate\Http\Request;

echo "=== TESTING AJAX ENDPOINT ===\n";

// Crear una instancia del controlador
$controller = new DriverTypeController();

// Simular una request AJAX
$request = new Request();
$request->headers->set('X-Requested-With', 'XMLHttpRequest');
$request->headers->set('Accept', 'application/json');

try {
    // Llamar al método index que debería devolver JSON
    $response = $controller->index($request);
    
    if ($response instanceof \Illuminate\Http\JsonResponse) {
        $data = $response->getData(true);
        echo "Response Status: " . $response->getStatusCode() . "\n";
        echo "Number of records: " . count($data) . "\n\n";
        
        if (count($data) > 0) {
            echo "First record structure:\n";
            print_r($data[0]);
        } else {
            echo "No data returned\n";
        }
    } else {
        echo "Response is not JSON\n";
        echo "Response type: " . get_class($response) . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}