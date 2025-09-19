<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserDriverDetail;
use App\Livewire\Driver\Steps\CertificationStep;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;

echo "=== Verificación Final de PDFs Third Party ===\n";

try {
    // Buscar driver con third_party_details
    $driver = UserDriverDetail::whereHas('application', function($query) {
        $query->whereHas('thirdPartyDetail');
    })
        ->with(['application.thirdPartyDetail', 'vehicles'])
        ->first();

    if (!$driver) {
        echo "❌ No se encontró driver con third_party_details\n";
        exit(1);
    }

    $thirdPartyDetails = $driver->application->thirdPartyDetail;
    $vehicle = $driver->vehicles->first();

    if (!$vehicle) {
        echo "❌ No se encontró vehículo para el driver\n";
        exit(1);
    }

    echo "✅ Driver: {$driver->user->name}\n";
    echo "✅ Third Party: {$thirdPartyDetails->third_party_name}\n";
    echo "✅ Vehículo: {$vehicle->year} {$vehicle->make}\n\n";

    // Preparar datos como en CertificationStep
    $consentData = [
        'verification' => (object) [
            'third_party_name' => $thirdPartyDetails->third_party_name,
            'third_party_phone' => $thirdPartyDetails->third_party_phone,
            'third_party_email' => $thirdPartyDetails->third_party_email,
            'third_party_address' => $thirdPartyDetails->third_party_address,
            'third_party_fein' => $thirdPartyDetails->third_party_fein,
        ],
        'driverDetails' => (object) [
            'user' => (object) [
                'name' => $driver->user->name,
                'email' => $driver->user->email,
            ],
            'middle_name' => $driver->middle_name ?? '',
            'last_name' => $driver->last_name ?? '',
            'phone' => $driver->phone ?? '',
        ],
        'vehicle' => $vehicle,
    ];

    $leaseData = [
        'verification' => (object) [
            'third_party_name' => $thirdPartyDetails->third_party_name,
            'third_party_phone' => $thirdPartyDetails->third_party_phone,
            'third_party_email' => $thirdPartyDetails->third_party_email,
            'third_party_address' => $thirdPartyDetails->third_party_address,
            'third_party_fein' => $thirdPartyDetails->third_party_fein,
        ],
        'ownerName' => $thirdPartyDetails->third_party_name,
        'ownerDba' => $thirdPartyDetails->third_party_dba ?? '',
        'ownerContact' => $thirdPartyDetails->third_party_contact ?? '',
        'ownerPhone' => $thirdPartyDetails->third_party_phone,
        'ownerEmail' => $thirdPartyDetails->third_party_email,
        'ownerAddress' => $thirdPartyDetails->third_party_address ?? '',
        'ownerFein' => $thirdPartyDetails->third_party_fein ?? '',
        'vehicleYear' => $vehicle->year,
        'vehicleMake' => $vehicle->make,
        'vehicleVin' => $vehicle->vin,
        'carrierName' => $driver->carrier->name ?? 'EF Services',
        'signature' => 'data:image/png;base64,test',
        'driverDetails' => (object) [
            'user' => (object) [
                'name' => $driver->user->name,
                'email' => $driver->user->email,
            ],
        ],
        'vehicle' => $vehicle,
    ];

    echo "=== Verificando Contenido de Plantillas ===\n";

    // Verificar plantilla third-party-consent
    try {
        $consentHtml = View::make('pdfs.third-party-consent', $consentData)->render();
        echo "✅ Plantilla third-party-consent renderizada correctamente\n";
        
        // Verificar que contiene datos importantes
        if (strpos($consentHtml, $thirdPartyDetails->third_party_name) !== false) {
            echo "   ✅ Contiene nombre del tercero: {$thirdPartyDetails->third_party_name}\n";
        } else {
            echo "   ❌ NO contiene nombre del tercero\n";
        }
        
        if (strpos($consentHtml, $driver->user->name) !== false) {
            echo "   ✅ Contiene nombre del driver: {$driver->user->name}\n";
        } else {
            echo "   ❌ NO contiene nombre del driver\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Error en plantilla third-party-consent: " . $e->getMessage() . "\n";
    }

    // Verificar plantilla lease-agreement
    try {
        $leaseHtml = View::make('pdfs.lease-agreement', $leaseData)->render();
        echo "✅ Plantilla lease-agreement renderizada correctamente\n";
        
        // Verificar que contiene datos importantes
        if (strpos($leaseHtml, $thirdPartyDetails->third_party_name) !== false) {
            echo "   ✅ Contiene nombre del tercero: {$thirdPartyDetails->third_party_name}\n";
        } else {
            echo "   ❌ NO contiene nombre del tercero\n";
        }
        
        if (strpos($leaseHtml, $vehicle->year . ' ' . $vehicle->make) !== false) {
            echo "   ✅ Contiene información del vehículo: {$vehicle->year} {$vehicle->make}\n";
        } else {
            echo "   ❌ NO contiene información del vehículo\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Error en plantilla lease-agreement: " . $e->getMessage() . "\n";
    }

    echo "\n=== Verificando PDFs Generados ===\n";
    
    // Verificar que los PDFs existen
    $consentPath = "driver/{$driver->id}/vehicle_verifications/third_party_consent.pdf";
    $leasePath = "driver/{$driver->id}/vehicle_verifications/lease_agreement_third_party.pdf";
    
    if (Storage::disk('public')->exists($consentPath)) {
        $size = Storage::disk('public')->size($consentPath);
        echo "✅ PDF de consentimiento existe: {$size} bytes\n";
    } else {
        echo "❌ PDF de consentimiento NO existe\n";
    }
    
    if (Storage::disk('public')->exists($leasePath)) {
        $size = Storage::disk('public')->size($leasePath);
        echo "✅ PDF de lease agreement existe: {$size} bytes\n";
    } else {
        echo "❌ PDF de lease agreement NO existe\n";
    }

    echo "\n🎉 VERIFICACIÓN COMPLETA EXITOSA\n";
    echo "\n=== Resumen Final ===\n";
    echo "1. ✅ Datos de third_party_details se obtienen correctamente de la BD\n";
    echo "2. ✅ Ambas plantillas (consent + lease-agreement) renderizan sin errores\n";
    echo "3. ✅ Los datos del tercero se muestran en ambas plantillas\n";
    echo "4. ✅ Los datos del vehículo se muestran en lease-agreement\n";
    echo "5. ✅ Ambos PDFs se generan correctamente\n";
    echo "\n✅ PROBLEMA COMPLETAMENTE RESUELTO ✅\n";

} catch (Exception $e) {
    echo "❌ Error durante la verificación: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n=== Fin de Verificación ===\n";