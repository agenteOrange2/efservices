<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Admin\Driver\DriverApplicationDetail;

echo "Checking applying_position_other field in database...\n\n";

// Get all records with applying_position = 'other'
$details = DriverApplicationDetail::where('applying_position', 'other')->get();

if ($details->count() > 0) {
    echo "Found " . $details->count() . " records with applying_position = 'other':\n\n";
    
    foreach ($details as $detail) {
        echo "ID: " . $detail->id . "\n";
        echo "applying_position: " . $detail->applying_position . "\n";
        echo "applying_position_other: " . ($detail->applying_position_other ?? 'NULL') . "\n";
        echo "Created at: " . $detail->created_at . "\n";
        echo "Updated at: " . $detail->updated_at . "\n";
        echo "---\n";
    }
} else {
    echo "No records found with applying_position = 'other'\n";
}

// Get the latest record regardless of applying_position
$latest = DriverApplicationDetail::latest()->first();
if ($latest) {
    echo "\nLatest record in database:\n";
    echo "ID: " . $latest->id . "\n";
    echo "applying_position: " . $latest->applying_position . "\n";
    echo "applying_position_other: " . ($latest->applying_position_other ?? 'NULL') . "\n";
    echo "Created at: " . $latest->created_at . "\n";
    echo "Updated at: " . $latest->updated_at . "\n";
} else {
    echo "\nNo records found in database\n";
}