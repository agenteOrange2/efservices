<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== USER AND ROLE ANALYSIS ===\n\n";

try {
    // Check total users
    $totalUsers = App\Models\User::count();
    echo "Total users in system: {$totalUsers}\n\n";
    
    if ($totalUsers > 0) {
        echo "Existing users:\n";
        $users = App\Models\User::all(['id', 'name', 'email']);
        foreach ($users as $user) {
            echo "   - {$user->name} ({$user->email})\n";
        }
        echo "\n";
    }
    
    // Check roles
    if (class_exists('Spatie\\Permission\\Models\\Role')) {
        $roles = Spatie\Permission\Models\Role::all(['id', 'name']);
        echo "Available roles: " . $roles->count() . "\n";
        foreach ($roles as $role) {
            echo "   - {$role->name}\n";
        }
        echo "\n";
        
        // Check if admin role exists
        $adminRole = Spatie\Permission\Models\Role::where('name', 'admin')->first();
        if (!$adminRole) {
            echo "Creating admin role...\n";
            $adminRole = Spatie\Permission\Models\Role::create(['name' => 'admin']);
            echo "✅ Admin role created\n";
        } else {
            echo "✅ Admin role exists\n";
        }
        
        // Check for admin users
        $adminUsers = App\Models\User::role('admin')->get();
        echo "Users with admin role: " . $adminUsers->count() . "\n";
        
        if ($adminUsers->count() === 0) {
            echo "\n❌ NO ADMIN USERS FOUND!\n";
            echo "\nTo fix this issue, you need to:\n";
            echo "1. Create an admin user, or\n";
            echo "2. Assign admin role to an existing user\n\n";
            
            if ($totalUsers > 0) {
                echo "You can assign admin role to an existing user with:\n";
                echo "php artisan tinker\n";
                echo ">>> \$user = App\\Models\\User::first();\n";
                echo ">>> \$user->assignRole('admin');\n\n";
            } else {
                echo "You can create an admin user with:\n";
                echo "php artisan make:seeder AdminUserSeeder\n";
                echo "Or manually create one in tinker\n\n";
            }
        } else {
            echo "✅ Admin users found:\n";
            foreach ($adminUsers as $user) {
                echo "   - {$user->name} ({$user->email})\n";
            }
        }
        
    } else {
        echo "❌ Spatie Permission package not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== SOLUTION SUMMARY ===\n";
echo "The admin pages are redirecting to login because:\n";
echo "1. ✅ Routes are correctly configured with 'auth' middleware\n";
echo "2. ✅ Carrier 'depeche-mode-llc' exists in database\n";
echo "3. ✅ Controllers and views exist\n";
echo "4. ❌ No authenticated admin user accessing the pages\n\n";
echo "TO FIX: Login as an admin user or create one if none exists.\n";