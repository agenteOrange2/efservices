<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "Testing Driver Login and Dashboard Access..." . PHP_EOL . PHP_EOL;

// Encontrar el usuario driver de prueba
$user = User::find(49);

if (!$user) {
    echo "User not found!" . PHP_EOL;
    exit(1);
}

echo "User found: " . $user->email . PHP_EOL;
echo "User status: " . $user->status . PHP_EOL;

if ($user->driverDetails) {
    echo "Driver Details Status: " . $user->driverDetails->status . PHP_EOL;
    echo "Current Step: " . ($user->driverDetails->current_step ?? 'null') . PHP_EOL;
    echo "Application Completed: " . ($user->driverDetails->application_completed ? 'Yes' : 'No') . PHP_EOL;
}

if ($user->driverApplication) {
    echo "Driver Application Status: " . $user->driverApplication->status . PHP_EOL;
}

echo PHP_EOL . "--- Analysis ---" . PHP_EOL;

// Analizar el estado del usuario
if ($user->status != 1) {
    echo "❌ User is not active (status: {$user->status})" . PHP_EOL;
    echo "Expected: Should be redirected to login" . PHP_EOL;
} else {
    echo "✅ User is active" . PHP_EOL;
}

if (!$user->driverDetails) {
    echo "❌ No driver details found" . PHP_EOL;
    echo "Expected: Should be redirected to complete_registration" . PHP_EOL;
} else {
    if (!$user->driverDetails->application_completed) {
        echo "❌ Driver application not completed" . PHP_EOL;
        echo "Expected: Should be redirected to registration step {$user->driverDetails->current_step}" . PHP_EOL;
    } else {
        echo "✅ Driver application completed" . PHP_EOL;
    }
    
    if ($user->driverDetails->status != 1) {
        echo "❌ Driver details status is not active (status: {$user->driverDetails->status})" . PHP_EOL;
        echo "Expected: Should be redirected to driver.pending" . PHP_EOL;
    } else {
        echo "✅ Driver details status is active" . PHP_EOL;
    }
}

if (!$user->driverApplication) {
    echo "❌ No driver application found" . PHP_EOL;
    echo "Expected: Should create draft application and redirect" . PHP_EOL;
} else {
    echo "Driver Application Status: {$user->driverApplication->status}" . PHP_EOL;
    
    switch ($user->driverApplication->status) {
        case 'draft':
            echo "❌ Application is in draft status" . PHP_EOL;
            echo "Expected: Should be redirected to registration step" . PHP_EOL;
            break;
        case 'pending':
            echo "⏳ Application is pending approval" . PHP_EOL;
            echo "Expected: Should be redirected to driver.pending" . PHP_EOL;
            break;
        case 'rejected':
            echo "❌ Application was rejected" . PHP_EOL;
            echo "Expected: Should be redirected to driver.rejected" . PHP_EOL;
            break;
        case 'approved':
            echo "✅ Application is approved" . PHP_EOL;
            echo "Expected: Should check for required documents" . PHP_EOL;
            break;
    }
}

echo PHP_EOL . "--- Conclusion ---" . PHP_EOL;
echo "Based on the current user state, access to /driver/dashboard should be BLOCKED" . PHP_EOL;
echo "User should be redirected to appropriate registration step or status page" . PHP_EOL;