<?php

echo "=== ANALISIS PROFUNDO ADMIN CARRIER 'depeche-mode-llc' ===\n\n";

// 1. Verificar archivos de controladores
echo "1. VERIFICANDO CONTROLADORES ADMIN:\n";
$controllers = [
    'app/Http/Controllers/Admin/CarrierController.php',
    'app/Http/Controllers/Admin/UserCarrierController.php', 
    'app/Http/Controllers/Admin/UserDriverController.php',
    'app/Http/Controllers/Admin/CarrierDocumentController.php'
];

foreach ($controllers as $controller) {
    if (file_exists(__DIR__ . '/' . $controller)) {
        echo "   OK {$controller}\n";
    } else {
        echo "   ERROR {$controller} NO EXISTE\n";
    }
}

// 2. Verificar rutas admin
echo "\n2. VERIFICANDO ARCHIVO DE RUTAS ADMIN:\n";
if (file_exists(__DIR__ . '/routes/admin.php')) {
    echo "   OK routes/admin.php existe\n";
    $routeContent = file_get_contents(__DIR__ . '/routes/admin.php');
    
    if (strpos($routeContent, 'carrier/{carrier:slug}') !== false) {
        echo "     OK Ruta carrier con slug encontrada\n";
    } else {
        echo "     ERROR Ruta carrier con slug NO encontrada\n";
    }
    
    if (strpos($routeContent, 'user-carriers') !== false) {
        echo "     OK Ruta user-carriers encontrada\n";
    } else {
        echo "     ERROR Ruta user-carriers NO encontrada\n";
    }
    
    if (strpos($routeContent, 'drivers') !== false) {
        echo "     OK Ruta drivers encontrada\n";
    } else {
        echo "     ERROR Ruta drivers NO encontrada\n";
    }
    
    if (strpos($routeContent, 'documents') !== false) {
        echo "     OK Ruta documents encontrada\n";
    } else {
        echo "     ERROR Ruta documents NO encontrada\n";
    }
} else {
    echo "   ERROR routes/admin.php NO EXISTE\n";
}

// 3. Verificar bootstrap/app.php
echo "\n3. VERIFICANDO BOOTSTRAP/APP.PHP:\n";
if (file_exists(__DIR__ . '/bootstrap/app.php')) {
    echo "   OK bootstrap/app.php existe\n";
    $bootstrapContent = file_get_contents(__DIR__ . '/bootstrap/app.php');
    
    if (strpos($bootstrapContent, 'admin') !== false) {
        echo "     OK Configuracion admin encontrada\n";
    } else {
        echo "     ERROR Configuracion admin NO encontrada\n";
    }
    
    if (strpos($bootstrapContent, 'auth') !== false) {
        echo "     OK Middleware auth encontrado\n";
    } else {
        echo "     ERROR Middleware auth NO encontrado\n";
    }
} else {
    echo "   ERROR bootstrap/app.php NO EXISTE\n";
}

// 4. Verificar vistas admin
echo "\n4. VERIFICANDO VISTAS ADMIN:\n";
$adminViews = [
    'resources/views/admin/carriers/edit.blade.php',
    'resources/views/admin/carriers/show.blade.php', 
    'resources/views/admin/user-carriers/index.blade.php',
    'resources/views/admin/drivers/index.blade.php',
    'resources/views/admin/carrier-documents/index.blade.php'
];

foreach ($adminViews as $view) {
    if (file_exists(__DIR__ . '/' . $view)) {
        echo "   OK {$view}\n";
    } else {
        echo "   ERROR {$view} NO EXISTE\n";
    }
}

// 5. Verificar vista welcome
echo "\n5. VERIFICANDO VISTA WELCOME:\n";
if (file_exists(__DIR__ . '/resources/views/welcome.blade.php')) {
    echo "   OK welcome.blade.php existe\n";
    $welcomeContent = file_get_contents(__DIR__ . '/resources/views/welcome.blade.php');
    if (strpos($welcomeContent, 'Welcome to EF Services') !== false) {
        echo "     PROBLEMA: Contiene el texto que se muestra en las URLs admin\n";
        echo "     CAUSA: Las rutas admin estan mostrando welcome.blade.php\n";
    }
} else {
    echo "   ERROR welcome.blade.php NO EXISTE\n";
}

// 6. Verificar middleware
echo "\n6. VERIFICANDO MIDDLEWARE:\n";
$middlewareFiles = [
    'app/Http/Middleware/Authenticate.php',
    'app/Http/Middleware/CheckUserStatus.php',
    'app/Http/Middleware/CheckPermission.php'
];

foreach ($middlewareFiles as $middleware) {
    if (file_exists(__DIR__ . '/' . $middleware)) {
        echo "   OK {$middleware}\n";
    } else {
        echo "   ERROR {$middleware} NO EXISTE\n";
    }
}

echo "\n=== RESUMEN DE PROBLEMAS IDENTIFICADOS ===\n";
echo "\nProblemas principales encontrados:\n";
echo "\n1. PROBLEMA DE AUTENTICACION/AUTORIZACION:\n";
echo "   - Las rutas admin muestran welcome.blade.php\n";
echo "   - Esto indica falta de autenticacion o autorizacion\n";

echo "\n2. POSIBLES CAUSAS:\n";
echo "   - Usuario no autenticado (necesita login)\n";
echo "   - Usuario sin permisos de admin\n";
echo "   - Middleware mal configurado\n";
echo "   - Carrier 'depeche-mode-llc' no existe en BD\n";

echo "\n3. SOLUCIONES RECOMENDADAS:\n";
echo "   - Verificar autenticacion del usuario\n";
echo "   - Verificar permisos/roles del usuario\n";
echo "   - Verificar existencia del carrier en BD\n";
echo "   - Revisar configuracion de middleware\n";

echo "\n=== FIN DEL ANALISIS ===\n";