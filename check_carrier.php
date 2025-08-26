<?php

echo "=== CARRIER ANALYSIS ===\n\n";

// Check if carrier exists in database
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=efservices', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connection successful\n\n";
    
    // Check for specific carrier
    $stmt = $pdo->prepare("SELECT * FROM carriers WHERE slug = ?");
    $stmt->execute(['depeche-mode-llc']);
    $carrier = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($carrier) {
        echo "✅ Carrier 'depeche-mode-llc' EXISTS\n";
        echo "   - ID: {$carrier['id']}\n";
        echo "   - Name: {$carrier['name']}\n";
        echo "   - Status: {$carrier['status']}\n";
        echo "   - Created: {$carrier['created_at']}\n\n";
    } else {
        echo "❌ Carrier 'depeche-mode-llc' NOT FOUND\n\n";
        
        // Show all carriers
        echo "Available carriers:\n";
        $stmt = $pdo->query("SELECT id, name, slug, status FROM carriers LIMIT 10");
        $carriers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($carriers)) {
            echo "   - No carriers found in database\n";
        } else {
            foreach ($carriers as $c) {
                echo "   - {$c['id']}: {$c['name']} ({$c['slug']}) - Status: {$c['status']}\n";
            }
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n\n";
}

// Check files
echo "=== FILE ANALYSIS ===\n";

$files = [
    'routes/admin.php' => 'Admin routes file',
    'app/Http/Controllers/Admin/CarrierController.php' => 'Carrier Controller',
    'resources/views/admin/carrier/show.blade.php' => 'Carrier show view',
    'resources/views/admin/carrier/edit.blade.php' => 'Carrier edit view',
    'resources/views/admin/carrier/drivers.blade.php' => 'Carrier drivers view',
    'resources/views/admin/carrier/documents/index.blade.php' => 'Carrier documents view',
    'bootstrap/app.php' => 'Bootstrap configuration'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "✅ {$description}: EXISTS\n";
    } else {
        echo "❌ {$description}: MISSING\n";
    }
}

echo "\n=== ROUTE CONFIGURATION ===\n";

// Check bootstrap/app.php
if (file_exists('bootstrap/app.php')) {
    $content = file_get_contents('bootstrap/app.php');
    
    if (strpos($content, "routes/admin.php") !== false) {
        echo "✅ Admin routes are configured in bootstrap/app.php\n";
    } else {
        echo "❌ Admin routes NOT configured in bootstrap/app.php\n";
    }
    
    if (strpos($content, "middleware(['web', 'auth'])") !== false) {
        echo "✅ Auth middleware is configured for admin routes\n";
    } else {
        echo "❌ Auth middleware NOT properly configured\n";
    }
} else {
    echo "❌ bootstrap/app.php not found\n";
}

// Check routes/admin.php
if (file_exists('routes/admin.php')) {
    $content = file_get_contents('routes/admin.php');
    
    $routes = [
        'admin/carrier/{carrier}' => 'Carrier show route',
        'admin/carrier/{carrier}/edit' => 'Carrier edit route',
        'admin/carrier/{carrier}/user-carriers' => 'User carriers route',
        'admin/carrier/{carrier}/drivers' => 'Drivers route',
        'admin/carrier/{carrier}/documents' => 'Documents route'
    ];
    
    echo "\nChecking specific routes in admin.php:\n";
    foreach ($routes as $route => $description) {
        if (strpos($content, $route) !== false) {
            echo "✅ {$description}: FOUND\n";
        } else {
            echo "❌ {$description}: NOT FOUND\n";
        }
    }
}

echo "\n=== CONTROLLER METHODS ===\n";

if (file_exists('app/Http/Controllers/Admin/CarrierController.php')) {
    $content = file_get_contents('app/Http/Controllers/Admin/CarrierController.php');
    
    $methods = [
        'show' => 'Show method',
        'edit' => 'Edit method',
        'userCarriers' => 'User carriers method',
        'drivers' => 'Drivers method',
        'documents' => 'Documents method'
    ];
    
    foreach ($methods as $method => $description) {
        if (strpos($content, "function {$method}") !== false) {
            echo "✅ {$description}: EXISTS\n";
        } else {
            echo "❌ {$description}: MISSING\n";
        }
    }
}

echo "\n=== SUMMARY ===\n";
echo "Analysis complete. Check the results above for issues.\n";
echo "\nCommon issues that cause generic content:\n";
echo "1. Carrier not found in database\n";
echo "2. Authentication middleware not working\n";
echo "3. Missing controller methods\n";
echo "4. Missing or incorrect routes\n";
echo "5. Missing view files\n";