<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Carrier;
use App\Models\CarrierBankingDetail;

$carrier = Carrier::find(50);
if ($carrier) {
    echo "Carrier ID: " . $carrier->id . PHP_EOL;
    echo "Carrier Name: " . $carrier->name . PHP_EOL;
    
    $bankingDetails = $carrier->bankingDetails;
    if ($bankingDetails) {
        echo "Banking Details ID: " . $bankingDetails->id . PHP_EOL;
        echo "Account Holder: " . $bankingDetails->account_holder_name . PHP_EOL;
        echo "Status: " . $bankingDetails->status . PHP_EOL;
    } else {
        echo "NO BANKING DETAILS FOUND for this carrier!" . PHP_EOL;
        
        // Verificar si existe en la tabla directamente
        $directCheck = CarrierBankingDetail::where('carrier_id', 50)->first();
        if ($directCheck) {
            echo "BUT found banking details with direct query:" . PHP_EOL;
            echo "Banking Details ID: " . $directCheck->id . PHP_EOL;
            echo "Account Holder: " . $directCheck->account_holder_name . PHP_EOL;
        } else {
            echo "No banking details exist for carrier 50 in database." . PHP_EOL;
        }
    }
} else {
    echo "Carrier 50 not found!" . PHP_EOL;
}