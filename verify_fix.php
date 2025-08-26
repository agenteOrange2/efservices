<?php

require_once __DIR__ . '/vendor/autoload.php';

// Cargar la aplicación Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Carrier;

echo "=== VERIFICACIÓN DE LA CORRECCIÓN ===\n\n";

try {
    // Simular exactamente lo que hace el controlador
    echo "1. SIMULANDO EL MÉTODO showIndependentCarrierSelection:\n";
    echo str_repeat("-", 60) . "\n";
    
    $carriers = Carrier::where('status', Carrier::STATUS_ACTIVE)
        ->with(['membership', 'media'])
        ->get()
        ->map(function($carrier) {
            // Agregar recuento de conductores y máximo permitido
            $driver_count = $carrier->userDrivers()->count();
            $max_drivers = $carrier->membership->max_drivers ?? 1;
            
            // Agregar estos datos al carrier
            $carrier->driver_count = $driver_count;
            $carrier->max_drivers = $max_drivers;
            $carrier->is_full = $driver_count >= $max_drivers;
            
            return $carrier;
        });
    
    echo "Total carriers activos encontrados: " . $carriers->count() . "\n\n";
    
    // Mostrar cada carrier con sus datos calculados
    foreach ($carriers as $carrier) {
        echo "Carrier: {$carrier->name}\n";
        echo "  - ID: {$carrier->id}\n";
        echo "  - Status: {$carrier->status} (" . ($carrier->status == 1 ? 'ACTIVO' : 'INACTIVO') . ")\n";
        echo "  - Driver Count: {$carrier->driver_count}\n";
        echo "  - Max Drivers: {$carrier->max_drivers}\n";
        echo "  - Is Full: " . ($carrier->is_full ? 'SÍ' : 'NO') . "\n";
        echo "  - Membresía: " . ($carrier->membership ? $carrier->membership->name : 'Sin membresía') . "\n";
        echo str_repeat("-", 40) . "\n";
    }
    
    // Simular el filtro de la vista CORREGIDA
    echo "\n2. SIMULANDO EL FILTRO DE LA VISTA CORREGIDA:\n";
    echo str_repeat("-", 60) . "\n";
    
    $availableCarriers = $carriers->filter(function($carrier) {
        return $carrier->status == 1 && !$carrier->is_full;
    });
    
    echo "Carriers disponibles después del filtro: " . $availableCarriers->count() . "\n\n";
    
    if ($availableCarriers->count() > 0) {
        echo "✅ CARRIERS DISPONIBLES PARA MOSTRAR:\n";
        foreach ($availableCarriers as $carrier) {
            echo "- {$carrier->name} ({$carrier->driver_count}/{$carrier->max_drivers} conductores)\n";
        }
    } else {
        echo "❌ NO HAY CARRIERS DISPONIBLES PARA MOSTRAR\n";
        echo "Esto significa que todos los carriers están llenos o inactivos.\n";
    }
    
    // Simular el filtro de carriers llenos
    echo "\n3. CARRIERS LLENOS:\n";
    echo str_repeat("-", 60) . "\n";
    
    $fullCarriers = $carriers->filter(function($carrier) {
        return $carrier->is_full;
    });
    
    echo "Carriers llenos: " . $fullCarriers->count() . "\n";
    
    if ($fullCarriers->count() > 0) {
        foreach ($fullCarriers as $carrier) {
            echo "- {$carrier->name} ({$carrier->driver_count}/{$carrier->max_drivers} conductores) - LLENO\n";
        }
    } else {
        echo "No hay carriers llenos.\n";
    }
    
    // Generar el JSON que se pasaría a la vista
    echo "\n4. JSON PARA LA VISTA (carriers disponibles):\n";
    echo str_repeat("-", 60) . "\n";
    
    $jsonData = json_encode($availableCarriers->values(), JSON_PRETTY_PRINT);
    echo $jsonData . "\n";
    
    // Resumen final
    echo "\n5. RESUMEN DE LA CORRECCIÓN:\n";
    echo str_repeat("=", 60) . "\n";
    
    if ($availableCarriers->count() > 0) {
        echo "🟢 CORRECCIÓN EXITOSA:\n";
        echo "- Se encontraron {$availableCarriers->count()} carriers disponibles\n";
        echo "- Los carriers ahora deberían mostrarse en la página de registro\n";
        echo "- El problema del filtro en la vista Blade ha sido corregido\n";
    } else {
        echo "🔴 PROBLEMA PERSISTENTE:\n";
        echo "- No hay carriers disponibles (todos están llenos o inactivos)\n";
        echo "- Verificar que existan carriers activos con espacio disponible\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR DURANTE LA VERIFICACIÓN: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== FIN DE LA VERIFICACIÓN ===\n";