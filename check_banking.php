<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Carrier;

$carrier = Carrier::find(50);
if (!$carrier) {
    echo "Carrier not found\n";
    exit;
}

$banking = $carrier->bankingDetails;
if (!$banking) {
    echo "No banking details found\n";
    exit;
}

echo "Banking ID: " . $banking->id . "\n";
echo "Account Holder: " . ($banking->account_holder_name ?? 'NULL') . "\n";
echo "Account Number: " . ($banking->account_number ?? 'NULL') . "\n";
echo "Routing Number: " . ($banking->banking_routing_number ?? 'NULL') . "\n";
echo "Zip Code: " . ($banking->zip_code ?? 'NULL') . "\n";
echo "Security Code: " . ($banking->security_code ?? 'NULL') . "\n";
echo "Status: " . $banking->status . "\n";
echo "\nRaw values:\n";
echo "Raw Routing: " . ($banking->getRawOriginal('banking_routing_number') ?? 'NULL') . "\n";
echo "Raw Zip: " . ($banking->getRawOriginal('zip_code') ?? 'NULL') . "\n";
echo "Raw Security: " . ($banking->getRawOriginal('security_code') ?? 'NULL') . "\n";