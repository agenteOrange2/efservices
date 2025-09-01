<?php

/**
 * Prueba detallada de rutas de mantenimiento
 * Incluye verificación de contenido y headers
 */

echo "=== PRUEBA DETALLADA DE RUTAS ===\n\n";

$routes = [
    'Create' => 'http://localhost:8000/admin/maintenance/create',
    'Calendar' => 'http://localhost:8000/admin/maintenance/calendar', 
    'Reports' => 'http://localhost:8000/admin/maintenance/reports',
    'Edit (que funciona)' => 'http://localhost:8000/admin/maintenance/1/edit'
];

foreach ($routes as $name => $url) {
    echo "\n" . str_repeat('=', 50) . "\n";
    echo "PROBANDO: {$name}\n";
    echo "URL: {$url}\n";
    echo str_repeat('-', 50) . "\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        echo "❌ ERROR cURL: {$error}\n";
    } else {
        echo "✅ Código HTTP: {$httpCode}\n";
        echo "📄 Content-Type: {$contentType}\n";
        echo "⏱️  Tiempo: " . round($totalTime * 1000, 2) . "ms\n";
        
        // Verificar si contiene HTML típico de Laravel
        if (strpos($response, '<html') !== false) {
            echo "🌐 Respuesta contiene HTML válido\n";
        }
        
        if (strpos($response, 'Laravel') !== false) {
            echo "🔧 Aplicación Laravel detectada\n";
        }
        
        // Buscar errores comunes
        if (strpos($response, '404') !== false) {
            echo "⚠️  Contiene texto '404'\n";
        }
        
        if (strpos($response, 'Not Found') !== false) {
            echo "⚠️  Contiene 'Not Found'\n";
        }
    }
}

echo "\n" . str_repeat('=', 50) . "\n";
echo "VERIFICACIÓN DE RUTAS REGISTRADAS:\n";
echo str_repeat('-', 50) . "\n";

// Ejecutar artisan route:list para maintenance
system('php artisan route:list | findstr maintenance');

echo "\n\n=== FIN DE PRUEBAS DETALLADAS ===\n";