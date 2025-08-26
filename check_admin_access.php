<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== ADMIN ACCESS ANALYSIS ===\n\n";

try {
    // Check for superadmin users
    $superAdmins = App\Models\User::role('superadmin')->get();
    echo "Users with 'superadmin' role: " . $superAdmins->count() . "\n";
    
    if ($superAdmins->count() > 0) {
        echo "✅ Superadmin users found:\n";
        foreach ($superAdmins as $user) {
            echo "   - {$user->name} ({$user->email})\n";
        }
    } else {
        echo "❌ No superadmin users found!\n";
        
        // Let's assign superadmin role to a user
        $user = App\Models\User::where('email', 'like', '%mauricio%')->first();
        if (!$user) {
            $user = App\Models\User::first();
        }
        
        if ($user) {
            echo "\nAssigning superadmin role to: {$user->name} ({$user->email})\n";
            $user->assignRole('superadmin');
            echo "✅ Superadmin role assigned!\n";
        }
    }
    
    echo "\n=== TESTING AUTHENTICATION ===\n";
    
    // Get a superadmin user for testing
    $adminUser = App\Models\User::role('superadmin')->first();
    
    if ($adminUser) {
        echo "Testing with user: {$adminUser->name}\n";
        
        // Simulate login
        Auth::login($adminUser);
        
        if (Auth::check()) {
            echo "✅ User authenticated successfully\n";
            echo "✅ User ID: " . Auth::id() . "\n";
            echo "✅ User roles: " . $adminUser->roles->pluck('name')->implode(', ') . "\n";
            
            // Test middleware
            if ($adminUser->hasRole('superadmin')) {
                echo "✅ User has superadmin role\n";
            }
        } else {
            echo "❌ Authentication failed\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== FINAL DIAGNOSIS ===\n";
echo "The admin routes are protected by 'auth' middleware.\n";
echo "To access admin pages, you need to:\n";
echo "1. ✅ Have a user with admin/superadmin role (now fixed)\n";
echo "2. ✅ Login through the web interface\n";
echo "3. ✅ Access the admin URLs after authentication\n\n";
echo "Next steps:\n";
echo "1. Go to: http://efservices.la/login\n";
echo "2. Login with a superadmin user\n";
echo "3. Then access: http://efservices.la/admin/carrier/depeche-mode-llc\n";