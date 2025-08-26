<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverLicense;

echo "=== DEBUG: Image URLs ===\n\n";

// Find a driver with licenses
$userDriverDetail = UserDriverDetail::with('licenses')->first();
if (!$userDriverDetail) {
    echo "No driver found\n";
    exit;
}

echo "Driver ID: {$userDriverDetail->id}\n";
echo "Licenses count: " . $userDriverDetail->licenses->count() . "\n\n";

foreach ($userDriverDetail->licenses as $license) {
    echo "License ID: {$license->id}\n";
    echo "License Number: {$license->license_number}\n";
    
    // Check media collections
    $frontMedia = $license->getFirstMedia('license_front');
    $backMedia = $license->getFirstMedia('license_back');
    
    echo "Front Media: " . ($frontMedia ? 'EXISTS' : 'NOT FOUND') . "\n";
    if ($frontMedia) {
        echo "  - File name: {$frontMedia->file_name}\n";
        echo "  - Path: {$frontMedia->getPath()}\n";
        echo "  - URL: {$frontMedia->getUrl()}\n";
        echo "  - getFirstMediaUrl: {$license->getFirstMediaUrl('license_front')}\n";
        echo "  - File exists: " . (file_exists($frontMedia->getPath()) ? 'YES' : 'NO') . "\n";
    }
    
    echo "Back Media: " . ($backMedia ? 'EXISTS' : 'NOT FOUND') . "\n";
    if ($backMedia) {
        echo "  - File name: {$backMedia->file_name}\n";
        echo "  - Path: {$backMedia->getPath()}\n";
        echo "  - URL: {$backMedia->getUrl()}\n";
        echo "  - getFirstMediaUrl: {$license->getFirstMediaUrl('license_back')}\n";
        echo "  - File exists: " . (file_exists($backMedia->getPath()) ? 'YES' : 'NO') . "\n";
    }
    
    echo "\n" . str_repeat('-', 50) . "\n\n";
}

// Check APP_URL configuration
echo "APP_URL: " . config('app.url') . "\n";
echo "Storage URL: " . config('filesystems.disks.public.url') . "\n";
echo "Asset URL: " . asset('storage') . "\n";

// Check if storage link exists
$storageLinkPath = public_path('storage');
echo "Storage link exists: " . (is_link($storageLinkPath) || is_dir($storageLinkPath) ? 'YES' : 'NO') . "\n";
if (is_link($storageLinkPath)) {
    echo "Storage link target: " . readlink($storageLinkPath) . "\n";
}