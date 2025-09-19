<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Driver\DriverApplicationDetail;

// Buscar el conductor ID 4
$driver = UserDriverDetail::find(4);
if ($driver) {
    echo "Driver ID: {$driver->id}\n";
    echo "User ID: {$driver->user_id}\n";
    echo "Current Step: {$driver->current_step}\n";
    
    // Buscar la aplicación
    $application = $driver->application;
    if ($application) {
        echo "Application ID: {$application->id}\n";
        
        // Buscar los detalles de la aplicación
        $details = $application->details;
        if ($details) {
            echo "Applying Position: {$details->applying_position}\n";
            echo "Applying Location: {$details->applying_location}\n";
        } else {
            echo "No application details found\n";
        }
    } else {
        echo "No application found\n";
    }
} else {
    echo "Driver not found\n";
}