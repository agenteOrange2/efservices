<?php

require_once __DIR__ . '/vendor/autoload.php';

// Cargar la aplicación Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Carrier;
use App\Models\UserDriverDetail;
use App\Models\Membership;

echo "=== DEBUG CARRIERS - Análisis de Transportistas ===\n\n";

try {
    // 1. Mostrar todos los carriers existentes
    echo "1. TODOS LOS CARRIERS EN LA BASE DE DATOS:\n";
    echo str_repeat("-", 60) . "\n";
    
    $allCarriers = Carrier::with(['membership', 'userDrivers'])->get();
    
    if ($allCarriers->isEmpty()) {
        echo "❌ NO SE ENCONTRARON CARRIERS EN LA BASE DE DATOS\n\n";
    } else {
        foreach ($allCarriers as $carrier) {
            echo "ID: {$carrier->id}\n";
            echo "Nombre: {$carrier->name}\n";
            echo "Slug: {$carrier->slug}\n";
            echo "Status: {$carrier->status} " . ($carrier->status == 1 ? '(ACTIVO)' : '(INACTIVO)') . "\n";
            echo "Driver Count: " . $carrier->userDrivers->count() . "\n";
            echo "Membresía: " . ($carrier->membership ? $carrier->membership->name : 'Sin membresía') . "\n";
            echo "Max Drivers: " . ($carrier->membership ? $carrier->membership->max_drivers : 'N/A') . "\n";
            echo "Created At: {$carrier->created_at}\n";
            echo "Updated At: {$carrier->updated_at}\n";
            echo str_repeat("-", 40) . "\n";
        }
    }
    
    // 2. Verificar carriers activos (status = 1)
    echo "\n2. CARRIERS ACTIVOS (STATUS = 1):\n";
    echo str_repeat("-", 60) . "\n";
    
    $activeCarriers = Carrier::where('status', 1)->with(['membership', 'userDrivers'])->get();
    
    if ($activeCarriers->isEmpty()) {
        echo "❌ NO HAY CARRIERS ACTIVOS (status = 1)\n";
        echo "Esto explica por qué no se muestran en la página de registro.\n\n";
    } else {
        echo "✅ Se encontraron {$activeCarriers->count()} carriers activos:\n";
        foreach ($activeCarriers as $carrier) {
            $driverCount = $carrier->userDrivers->count();
            $maxDrivers = $carrier->membership ? $carrier->membership->max_drivers : 1;
            $isFull = $driverCount >= $maxDrivers;
            
            echo "- {$carrier->name} (ID: {$carrier->id})\n";
            echo "  Conductores: {$driverCount}/{$maxDrivers} " . ($isFull ? '(LLENO)' : '(DISPONIBLE)') . "\n";
        }
    }
    
    // 3. Probar la consulta exacta del controlador
    echo "\n3. SIMULANDO CONSULTA DEL CONTROLADOR:\n";
    echo str_repeat("-", 60) . "\n";
    
    $controllerQuery = Carrier::where('status', Carrier::STATUS_ACTIVE)
        ->with(['membership', 'media'])
        ->get()
        ->map(function ($carrier) {
            $driverCount = $carrier->userDrivers()->count();
            $maxDrivers = $carrier->membership->max_drivers ?? 1;
            
            return [
                'id' => $carrier->id,
                'name' => $carrier->name,
                'slug' => $carrier->slug,
                'status' => $carrier->status,
                'driver_count' => $driverCount,
                'max_drivers' => $maxDrivers,
                'is_full' => $driverCount >= $maxDrivers,
                'membership' => $carrier->membership ? $carrier->membership->name : null
            ];
        });
    
    if ($controllerQuery->isEmpty()) {
        echo "❌ LA CONSULTA DEL CONTROLADOR NO DEVUELVE RESULTADOS\n";
        echo "Verificar:\n";
        echo "- Que existan carriers con status = 1\n";
        echo "- Que la constante STATUS_ACTIVE esté definida correctamente\n";
    } else {
        echo "✅ La consulta del controlador devuelve {$controllerQuery->count()} carriers:\n";
        foreach ($controllerQuery as $data) {
            echo "- {$data['name']}: {$data['driver_count']}/{$data['max_drivers']} conductores\n";
        }
    }
    
    // 4. Verificar constantes del modelo Carrier
    echo "\n4. CONSTANTES DEL MODELO CARRIER:\n";
    echo str_repeat("-", 60) . "\n";
    
    $reflection = new ReflectionClass(Carrier::class);
    $constants = $reflection->getConstants();
    
    foreach ($constants as $name => $value) {
        if (strpos($name, 'STATUS_') === 0) {
            echo "{$name} = {$value}\n";
        }
    }
    
    // 5. Verificar relación userDrivers
    echo "\n5. VERIFICACIÓN DE RELACIÓN userDrivers():\n";
    echo str_repeat("-", 60) . "\n";
    
    if (!$allCarriers->isEmpty()) {
        $firstCarrier = $allCarriers->first();
        try {
            $userDriversCount = $firstCarrier->userDrivers()->count();
            echo "✅ Relación userDrivers() funciona correctamente\n";
            echo "Carrier '{$firstCarrier->name}' tiene {$userDriversCount} conductores\n";
        } catch (Exception $e) {
            echo "❌ Error en relación userDrivers(): " . $e->getMessage() . "\n";
        }
    }
    
    // 6. Verificar tabla de membresías
    echo "\n6. VERIFICACIÓN DE MEMBRESÍAS:\n";
    echo str_repeat("-", 60) . "\n";
    
    $memberships = Membership::all();
    if ($memberships->isEmpty()) {
        echo "⚠️  No hay membresías en la base de datos\n";
    } else {
        echo "✅ Se encontraron {$memberships->count()} membresías:\n";
        foreach ($memberships as $membership) {
            echo "- {$membership->name}: max {$membership->max_drivers} conductores\n";
        }
    }
    
    // 7. Resumen y recomendaciones
    echo "\n7. RESUMEN Y RECOMENDACIONES:\n";
    echo str_repeat("=", 60) . "\n";
    
    if ($allCarriers->isEmpty()) {
        echo "🔴 PROBLEMA CRÍTICO: No hay carriers en la base de datos\n";
        echo "SOLUCIÓN: Ejecutar el seeder de carriers\n";
        echo "Comando: php artisan db:seed --class=CarrierSeeder\n";
    } elseif ($activeCarriers->isEmpty()) {
        echo "🔴 PROBLEMA: No hay carriers activos (status = 1)\n";
        echo "SOLUCIÓN: Activar carriers existentes o crear nuevos\n";
        echo "SQL: UPDATE carriers SET status = 1 WHERE id IN (1,2,3);\n";
    } else {
        echo "🟢 Hay carriers activos disponibles\n";
        echo "El problema puede estar en:\n";
        echo "- La vista Blade (verificar sintaxis Alpine.js)\n";
        echo "- El JavaScript del frontend\n";
        echo "- Caché de la aplicación\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR DURANTE LA EJECUCIÓN: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== FIN DEL DEBUG ===\n";