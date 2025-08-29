<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverApplication;

echo "Searching for driver users...\n";

$users = User::whereHas('roles', function($q) {
    $q->where('name', 'user_driver');
})->take(5)->get();

foreach($users as $user) {
    echo "\n--- User: {$user->email} (ID: {$user->id}) ---\n";
    echo "Status: {$user->status}\n";
    
    if($user->driverDetails) {
        echo "Driver Details: Yes\n";
        echo "  - Status: {$user->driverDetails->status}\n";
        echo "  - Current Step: " . ($user->driverDetails->current_step ?? 'null') . "\n";
        echo "  - Application Completed: " . ($user->driverDetails->application_completed ? 'Yes' : 'No') . "\n";
    } else {
        echo "Driver Details: No\n";
    }
    
    if($user->driverApplication) {
        echo "Driver Application: Yes\n";
        echo "  - Status: {$user->driverApplication->status}\n";
    } else {
        echo "Driver Application: No\n";
    }
}

if($users->isEmpty()) {
    echo "No driver users found\n";
}