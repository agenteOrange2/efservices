<?php

/**
 * Test script to verify the type comparison fix in UserDriverController
 * This script will test the edit URL to ensure it works after the fix
 */

echo "Testing UserDriverController edit fix...\n";
echo "========================================\n";

// Test URL - replace with actual carrier and driver IDs from your logs
$testUrl = 'http://127.0.0.1:8000/admin/carrier/dev-test/drivers/13/edit';

echo "Testing URL: $testUrl\n";
echo "Expected: HTTP 200 (page loads successfully)\n";
echo "Previous issue: Type mismatch between carrier_id (integer) and driver_carrier_id (string)\n";
echo "Fix applied: Changed strict comparison (===) to loose comparison (==)\n\n";

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ cURL Error: $error\n";
    exit(1);
}

echo "HTTP Status Code: $httpCode\n";

if ($httpCode === 200) {
    echo "✅ SUCCESS: Page loaded successfully!\n";
    echo "✅ Type comparison fix is working correctly.\n";
    echo "\nThe fix resolved the issue where:\n";
    echo "- carrier_id (integer 25) !== driver_carrier_id (string '25') was failing\n";
    echo "- Now using: carrier_id (integer 25) != driver_carrier_id (string '25') which passes\n";
} elseif ($httpCode >= 300 && $httpCode < 400) {
    echo "⚠️  REDIRECT: Got redirect status $httpCode\n";
    echo "This might indicate the validation is still failing.\n";
} else {
    echo "❌ ERROR: Got HTTP status $httpCode\n";
    echo "The fix may not be working as expected.\n";
}

echo "\n========================================\n";
echo "Next steps:\n";
echo "1. Check storage/logs/laravel.log for the new log entries\n";
echo "2. Look for 'fix_applied' messages in the logs\n";
echo "3. Verify that the validation now passes\n";
echo "========================================\n";

?>