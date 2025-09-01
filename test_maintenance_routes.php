<?php

/**
 * Script de prueba para verificar las rutas de mantenimiento
 * Este script verifica que las rutas estén funcionando correctamente
 */

echo "=== PRUEBA DE RUTAS DE MANTENIMIENTO ===\n\n";

// URLs a probar
$routes = [
    'Create' => 'http://localhost:8000/admin/maintenance/create',
    'Calendar' => 'http://localhost:8000/admin/maintenance/calendar', 
    'Reports' => 'http://localhost:8000/admin/maintenance/reports'
];

foreach ($routes as $name => $url) {
    echo "Probando ruta {$name}: {$url}\n";
    
    // Inicializar cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_NOBODY, true); // Solo obtener headers
    
    // Ejecutar la petición
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    // Mostrar resultado
    if ($error) {
        echo "  ❌ ERROR: {$error}\n";
    } else {
        if ($httpCode == 200) {
            echo "  ✅ OK - Código HTTP: {$httpCode}\n";
        } elseif ($httpCode == 302) {
            echo "  ⚠️  REDIRECT - Código HTTP: {$httpCode} (posible redirección a login)\n";
        } elseif ($httpCode == 404) {
            echo "  ❌ NOT FOUND - Código HTTP: {$httpCode}\n";
        } else {
            echo "  ⚠️  Código HTTP: {$httpCode}\n";
        }
    }
    
    echo "\n";
}

echo "=== FIN DE PRUEBAS ===\n";