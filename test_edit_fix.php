<?php

/**
 * Script de prueba para verificar la corrección del método edit
 * del UserDriverController
 */

echo "=== Test Script para UserDriverController::edit ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// URL de prueba
$testUrl = 'http://127.0.0.1:8000/admin/carrier/25/drivers/13/edit';

echo "Probando URL: $testUrl\n";
echo "Esperamos ver en los logs:\n";
echo "1. 'UserDriverController::edit - Validando ownership del driver'\n";
echo "2. 'UserDriverController::edit - Validación de ownership completada exitosamente'\n";
echo "3. 'DriverRegistrationManager::mount - Componente iniciado'\n\n";

// Ejecutar curl
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "Resultado del test:\n";
echo "HTTP Code: $httpCode\n";

if ($error) {
    echo "Error cURL: $error\n";
} else {
    echo "Respuesta recibida: " . strlen($response) . " bytes\n";
    
    if ($httpCode === 200) {
        echo "✅ SUCCESS: La página se cargó correctamente\n";
        echo "Revisa los logs en storage/logs/laravel.log para ver los detalles de validación\n";
    } elseif ($httpCode === 302) {
        echo "⚠️  REDIRECT: La página redirigió (posible error de validación)\n";
        echo "Revisa los logs para ver si la validación falló\n";
    } else {
        echo "❌ ERROR: HTTP Code $httpCode\n";
    }
}

echo "\n=== Fin del test ===\n";
echo "Para ver los logs detallados ejecuta:\n";
echo "Get-Content storage/logs/laravel.log | Select-Object -Last 20\n";