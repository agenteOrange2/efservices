<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Admin\Driver\DriverApplication;
use App\Models\UserDriverDetail;

echo "=== Testing Third Party Data Loading ===\n";

// Test 1: Check if DriverApplication 3 has thirdPartyDetail
$app = DriverApplication::find(3);
if ($app) {
    echo "Application ID 3 found\n";
    echo "User ID: " . $app->user_id . "\n";
    
    if ($app->thirdPartyDetail) {
        echo "Third Party Detail found:\n";
        echo "- DBA: " . $app->thirdPartyDetail->third_party_dba . "\n";
        echo "- Address: " . $app->thirdPartyDetail->third_party_address . "\n";
        echo "- Contact: " . $app->thirdPartyDetail->third_party_contact . "\n";
        echo "- FEIN: " . $app->thirdPartyDetail->third_party_fein . "\n";
    } else {
        echo "No Third Party Detail found\n";
    }
} else {
    echo "Application ID 3 not found\n";
}

// Test 2: Check UserDriverDetail relationship
$userDriver = UserDriverDetail::where('user_id', 51)->first();
if ($userDriver) {
    echo "\nUserDriverDetail found for user 51\n";
    echo "Driver ID: " . $userDriver->id . "\n";
    
    $application = $userDriver->application;
    if ($application) {
        echo "Application found via relationship: " . $application->id . "\n";
        
        if ($application->thirdPartyDetail) {
            echo "Third Party data accessible via relationship:\n";
            echo "- DBA: " . $application->thirdPartyDetail->third_party_dba . "\n";
        } else {
            echo "No Third Party Detail via relationship\n";
        }
    } else {
        echo "No application found via relationship\n";
    }
} else {
    echo "\nNo UserDriverDetail found for user 51\n";
}

echo "\n=== Test Complete ===\n";