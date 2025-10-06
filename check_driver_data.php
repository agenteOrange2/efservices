<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DRIVER APPLICATION DETAILS ===\n";
$details = App\Models\Admin\Driver\DriverApplicationDetail::take(3)->get();
foreach($details as $detail) {
    echo "ID: {$detail->id}, App ID: {$detail->driver_application_id}, Driver Type: {$detail->driver_type}\n";
}

echo "\n=== THIRD PARTY DETAILS ===\n";
$thirdParty = App\Models\ThirdPartyDetail::take(3)->get();
foreach($thirdParty as $tp) {
    echo "ID: {$tp->id}, Detail ID: {$tp->driver_application_detail_id}, Company: {$tp->company_name}\n";
}

echo "\n=== OWNER OPERATOR DETAILS ===\n";
$ownerOperator = App\Models\OwnerOperatorDetail::take(3)->get();
foreach($ownerOperator as $oo) {
    echo "ID: {$oo->id}, Detail ID: {$oo->driver_application_detail_id}, Vehicle ID: {$oo->vehicle_id}\n";
}

echo "\n=== DRIVER APPLICATIONS WITH RELATIONSHIPS ===\n";
$applications = App\Models\Admin\Driver\DriverApplication::with(['details', 'ownerOperatorDetail', 'thirdPartyDetail'])->take(3)->get();
foreach($applications as $app) {
    echo "App ID: {$app->id}, User ID: {$app->user_id}, Status: {$app->status}\n";
    if($app->details) {
        echo "  - Has details: Driver Type = {$app->details->driver_type}\n";
    }
    if($app->ownerOperatorDetail) {
        echo "  - Has Owner Operator Detail\n";
    }
    if($app->thirdPartyDetail) {
        echo "  - Has Third Party Detail\n";
    }
    echo "\n";
}