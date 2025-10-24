<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Admin\Driver\DriverApplicationDetail;

$detail = DriverApplicationDetail::latest()->first();

if ($detail) {
    echo "Latest applying_position: " . $detail->applying_position . PHP_EOL;
    echo "ID: " . $detail->id . PHP_EOL;
    echo "Created at: " . $detail->created_at . PHP_EOL;
} else {
    echo "No driver application details found." . PHP_EOL;
}