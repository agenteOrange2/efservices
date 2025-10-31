<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Carrier;
use App\Http\Controllers\Admin\CarrierController;
use Illuminate\Http\Request;

// Simular el método show del CarrierController
$carrier = Carrier::find(50);
if ($carrier) {
    echo "=== DEBUGGING BANKING DETAILS FOR CARRIER 50 ===" . PHP_EOL;
    echo "Carrier ID: " . $carrier->id . PHP_EOL;
    echo "Carrier Name: " . $carrier->name . PHP_EOL;
    echo PHP_EOL;
    
    // Obtener bankingDetails como lo hace el controlador
    $bankingDetails = $carrier->bankingDetails;
    
    echo "=== BANKING DETAILS ANALYSIS ===" . PHP_EOL;
    echo "isset(\$bankingDetails): " . (isset($bankingDetails) ? 'YES' : 'NO') . PHP_EOL;
    echo "is_null(\$bankingDetails): " . (is_null($bankingDetails) ? 'YES' : 'NO') . PHP_EOL;
    echo "empty(\$bankingDetails): " . (empty($bankingDetails) ? 'YES' : 'NO') . PHP_EOL;
    echo "Type of \$bankingDetails: " . gettype($bankingDetails) . PHP_EOL;
    
    if (is_object($bankingDetails)) {
        echo "Class of \$bankingDetails: " . get_class($bankingDetails) . PHP_EOL;
        echo "Banking Details ID: " . $bankingDetails->id . PHP_EOL;
        echo "Account Holder: " . $bankingDetails->account_holder_name . PHP_EOL;
        echo "Account Number: " . substr($bankingDetails->account_number, 0, 4) . '****' . PHP_EOL;
        echo "Status: " . $bankingDetails->status . PHP_EOL;
        echo "Created At: " . $bankingDetails->created_at . PHP_EOL;
        echo "Updated At: " . $bankingDetails->updated_at . PHP_EOL;
    }
    
    echo PHP_EOL;
    echo "=== CONDITION CHECKS ===" . PHP_EOL;
    echo "isset(\$bankingDetails) && !is_null(\$bankingDetails) && \$bankingDetails: " . 
         ((isset($bankingDetails) && !is_null($bankingDetails) && $bankingDetails) ? 'TRUE' : 'FALSE') . PHP_EOL;
    
    // Esta es la condición exacta usada en la vista
    if (isset($bankingDetails) && !is_null($bankingDetails) && $bankingDetails) {
        echo "✅ CONDITION PASSED - Banking details should be displayed" . PHP_EOL;
    } else {
        echo "❌ CONDITION FAILED - Banking details will NOT be displayed" . PHP_EOL;
        echo "   This means the 'No banking information' message will show instead" . PHP_EOL;
    }
    
} else {
    echo "Carrier 50 not found!" . PHP_EOL;
}