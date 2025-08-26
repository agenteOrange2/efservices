<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\Log;

echo "=== TESTING IMAGE PREVIEW FUNCTIONALITY ===\n\n";

// Get the UserDriverDetail with ID 1
$userDriverDetail = UserDriverDetail::find(1);

if (!$userDriverDetail) {
    echo "UserDriverDetail with ID 1 not found.\n";
    exit(1);
}

echo "UserDriverDetail found: ID {$userDriverDetail->id}\n";
echo "User ID: {$userDriverDetail->user_id}\n\n";

// Test front license image
echo "=== FRONT LICENSE IMAGE ===\n";
$frontMedia = $userDriverDetail->getFirstMedia('license_front');
if ($frontMedia) {
    $frontUrl = $userDriverDetail->getFirstMediaUrl('license_front');
    $frontFileName = $frontMedia->file_name;
    
    echo "Media ID: {$frontMedia->id}\n";
    echo "File Name: {$frontFileName}\n";
    echo "Generated URL: {$frontUrl}\n";
    echo "Full Path: {$frontMedia->getPath()}\n";
    echo "File Exists: " . (file_exists($frontMedia->getPath()) ? 'YES' : 'NO') . "\n";
    
    // Test if URL is accessible
    $headers = @get_headers($frontUrl);
    echo "URL Accessible: " . ($headers && strpos($headers[0], '200') !== false ? 'YES' : 'NO') . "\n";
} else {
    echo "No front license image found.\n";
}

echo "\n=== BACK LICENSE IMAGE ===\n";
$backMedia = $userDriverDetail->getFirstMedia('license_back');
if ($backMedia) {
    $backUrl = $userDriverDetail->getFirstMediaUrl('license_back');
    $backFileName = $backMedia->file_name;
    
    echo "Media ID: {$backMedia->id}\n";
    echo "File Name: {$backFileName}\n";
    echo "Generated URL: {$backUrl}\n";
    echo "Full Path: {$backMedia->getPath()}\n";
    echo "File Exists: " . (file_exists($backMedia->getPath()) ? 'YES' : 'NO') . "\n";
    
    // Test if URL is accessible
    $headers = @get_headers($backUrl);
    echo "URL Accessible: " . ($headers && strpos($headers[0], '200') !== false ? 'YES' : 'NO') . "\n";
} else {
    echo "No back license image found.\n";
}

echo "\n=== TESTING COMPONENT DATA ===\n";

// Simulate what LicenseStep.php does
$licenses = [];
if ($userDriverDetail->licenses->count() > 0) {
    foreach ($userDriverDetail->licenses as $license) {
        $licenseData = [
            'id' => $license->id,
            'front_preview' => $userDriverDetail->getFirstMediaUrl('license_front'),
            'back_preview' => $userDriverDetail->getFirstMediaUrl('license_back'),
            'front_filename' => $userDriverDetail->getFirstMedia('license_front')?->file_name ?? '',
            'back_filename' => $userDriverDetail->getFirstMedia('license_back')?->file_name ?? '',
        ];
        $licenses[] = $licenseData;
        
        echo "License ID: {$license->id}\n";
        echo "Front Preview URL: {$licenseData['front_preview']}\n";
        echo "Back Preview URL: {$licenseData['back_preview']}\n";
        echo "Front Filename: {$licenseData['front_filename']}\n";
        echo "Back Filename: {$licenseData['back_filename']}\n";
    }
} else {
    echo "No licenses found for this UserDriverDetail.\n";
}

echo "\n=== CONFIGURATION CHECK ===\n";
echo "APP_URL: " . config('app.url') . "\n";
echo "Storage URL: " . config('filesystems.disks.public.url') . "\n";
echo "Asset URL: " . asset('storage') . "\n";

echo "\n=== TEST COMPLETED ===\n";