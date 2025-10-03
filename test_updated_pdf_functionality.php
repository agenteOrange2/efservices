<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserDriverDetail;
use App\Livewire\Admin\Driver\DriverCertificationStep;
use Illuminate\Support\Facades\Log;

echo "=== PRUEBA DE FUNCIONALIDAD ACTUALIZADA DE PDFs ===\n\n";

// Crear una instancia del componente
$component = new DriverCertificationStep();

// Obtener algunos conductores para probar
$drivers = UserDriverDetail::with('user')
    ->whereNotNull('use_custom_dates')
    ->limit(3)
    ->get();

if ($drivers->isEmpty()) {
    echo "No se encontraron conductores con configuración de fechas personalizadas.\n";
    exit;
}

foreach ($drivers as $driver) {
    echo "\n--- CONDUCTOR ID: {$driver->id} ---\n";
    echo "Nombre: " . ($driver->user->name ?? 'N/A') . "\n";
    echo "use_custom_dates: " . ($driver->use_custom_dates ? 'true' : 'false') . "\n";
    echo "custom_created_at: " . ($driver->custom_created_at ?? 'NULL') . "\n";
    echo "created_at: {$driver->created_at}\n";
    echo "updated_at: {$driver->updated_at}\n";
    
    // Probar el método getEffectiveDates usando reflexión
    $reflection = new ReflectionClass($component);
    $method = $reflection->getMethod('getEffectiveDates');
    $method->setAccessible(true);
    
    try {
        $effectiveDates = $method->invoke($component, $driver->id);
        
        echo "\nRESULTADOS getEffectiveDates:\n";
        echo "- show_created_at: " . ($effectiveDates['show_created_at'] ? 'true' : 'false') . "\n";
        echo "- show_custom_created_at: " . ($effectiveDates['show_custom_created_at'] ? 'true' : 'false') . "\n";
        echo "- created_at: " . ($effectiveDates['created_at'] ?? 'NULL') . "\n";
        echo "- custom_created_at: " . ($effectiveDates['custom_created_at'] ?? 'NULL') . "\n";
        echo "- updated_at: " . ($effectiveDates['updated_at'] ?? 'NULL') . "\n";
        
        // Simular la lógica de formatted_dates como en los métodos actualizados
        $formattedDates = [
            'updated_at' => $effectiveDates['updated_at']->format('m/d/Y'),
            'updated_at_long' => $effectiveDates['updated_at']->format('F j, Y')
        ];
        
        // Siempre incluir created_at si show_created_at es true
        if ($effectiveDates['show_created_at']) {
            $formattedDates['created_at'] = $effectiveDates['created_at']->format('m/d/Y');
            $formattedDates['created_at_long'] = $effectiveDates['created_at']->format('F j, Y');
        }
        
        // Incluir custom_created_at solo si show_custom_created_at es true y tiene valor
        if ($effectiveDates['show_custom_created_at'] && $effectiveDates['custom_created_at']) {
            $formattedDates['custom_created_at'] = $effectiveDates['custom_created_at']->format('m/d/Y');
            $formattedDates['custom_created_at_long'] = $effectiveDates['custom_created_at']->format('F j, Y');
        }
        
        echo "\nFORMATTED_DATES que se pasarían a los PDFs:\n";
        foreach ($formattedDates as $key => $value) {
            echo "- {$key}: {$value}\n";
        }
        
        // Verificar la lógica según los requisitos del usuario
        echo "\nVERIFICACIÓN DE LÓGICA:\n";
        
        // created_at siempre debe estar visible
        if (isset($formattedDates['created_at'])) {
            echo "✓ created_at está visible (correcto)\n";
        } else {
            echo "✗ created_at NO está visible (incorrecto)\n";
        }
        
        // custom_created_at solo debe estar visible si use_custom_dates=true Y custom_created_at tiene valor
        if ($driver->use_custom_dates && $driver->custom_created_at) {
            if (isset($formattedDates['custom_created_at'])) {
                echo "✓ custom_created_at está visible cuando debe estarlo (correcto)\n";
            } else {
                echo "✗ custom_created_at NO está visible cuando debería estarlo (incorrecto)\n";
            }
        } else {
            if (!isset($formattedDates['custom_created_at'])) {
                echo "✓ custom_created_at NO está visible cuando no debe estarlo (correcto)\n";
            } else {
                echo "✗ custom_created_at está visible cuando NO debería estarlo (incorrecto)\n";
            }
        }
        
    } catch (Exception $e) {
        echo "Error al probar getEffectiveDates: " . $e->getMessage() . "\n";
    }
    
    echo "\n" . str_repeat('-', 50) . "\n";
}

echo "\n=== PRUEBA COMPLETADA ===\n";