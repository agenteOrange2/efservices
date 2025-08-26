<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use App\Models\Carrier;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use ReflectionClass;
use ReflectionMethod;
use Exception;

class DebugAdminAccess extends Command
{
    protected $signature = 'debug:admin-access';
    protected $description = 'Debug admin access issues for carrier pages';

    public function handle()
    {
        $this->info('=== ADMIN ACCESS DEBUGGING REPORT ===');
        $this->newLine();

        try {
            // 1. Check authentication status
            $this->info('1. AUTHENTICATION STATUS:');
            $this->line('   - Session driver: ' . config('session.driver'));
            $this->line('   - Auth guard: ' . config('auth.defaults.guard'));
            $this->line('   - Auth provider: ' . config('auth.defaults.provider'));
            
            // Check auth middleware configuration
            $router = app('router');
            $authMiddleware = $router->getMiddleware();
            $this->line('   - Auth middleware registered: ' . (isset($authMiddleware['auth']) ? 'Yes' : 'No'));
            
            $this->newLine();
            $this->info('2. USER ROLES AND PERMISSIONS:');
            
            // Check if Spatie Permission package is installed
            if (class_exists('Spatie\\Permission\\Models\\Role')) {
                $roles = Role::all();
                $this->line('   - Available roles: ' . $roles->pluck('name')->implode(', '));
                
                $permissions = Permission::all();
                $this->line('   - Available permissions: ' . $permissions->count() . ' permissions found');
                
                // Show some key admin permissions
                $adminPermissions = $permissions->filter(function($perm) {
                    return str_contains($perm->name, 'admin') || str_contains($perm->name, 'carrier');
                });
                $this->line('   - Admin/Carrier permissions: ' . $adminPermissions->pluck('name')->take(5)->implode(', '));
            } else {
                $this->line('   - Spatie Permission package: Not installed');
            }
            
            $this->newLine();
            $this->info('3. CARRIER EXISTENCE CHECK:');
            
            // Check if the specific carrier exists
            $carrier = Carrier::where('slug', 'depeche-mode-llc')->first();
            if ($carrier) {
                $this->line('   - Carrier \'depeche-mode-llc\': EXISTS');
                $this->line('   - Carrier ID: ' . $carrier->id);
                $this->line('   - Carrier Name: ' . $carrier->name);
                $this->line('   - Carrier Status: ' . ($carrier->status ? 'Active' : 'Inactive'));
                $this->line('   - Created: ' . $carrier->created_at);
            } else {
                $this->error('   - Carrier \'depeche-mode-llc\': NOT FOUND');
            }
            
            $this->newLine();
            $this->info('4. ADMIN MIDDLEWARE CONFIGURATION:');
            
            // Check middleware configuration
            $middlewareGroups = $router->getMiddlewareGroups();
            
            $this->line('   - Web middleware group: ' . (isset($middlewareGroups['web']) ? 'Configured' : 'Missing'));
            if (isset($middlewareGroups['web'])) {
                $this->line('     Components: ' . implode(', ', $middlewareGroups['web']));
            }
            
            $this->line('   - Auth middleware: ' . (isset($authMiddleware['auth']) ? 'Configured' : 'Missing'));
            
            // Check for custom admin middleware
            $customMiddleware = ['check.user.status', 'CheckPermission', 'admin', 'role', 'permission'];
            foreach ($customMiddleware as $middleware) {
                $this->line('   - ' . $middleware . ' middleware: ' . (isset($authMiddleware[$middleware]) ? 'Registered' : 'Not registered'));
            }
            
            $this->newLine();
            $this->info('5. ADMIN ROUTES REGISTRATION:');
            
            // Check if admin routes are registered
            $routes = $router->getRoutes();
            $adminRoutes = [];
            
            foreach ($routes as $route) {
                $uri = $route->uri();
                if (str_starts_with($uri, 'admin/carrier')) {
                    $adminRoutes[] = $uri;
                }
            }
            
            $this->line('   - Admin carrier routes found: ' . count($adminRoutes));
            if (count($adminRoutes) > 0) {
                $this->line('   - Sample routes:');
                foreach (array_slice($adminRoutes, 0, 5) as $route) {
                    $this->line('     * ' . $route);
                }
            }
            
            // Check specific routes
            $specificRoutes = [
                'admin/carrier/{carrier:slug}',
                'admin/carrier/{carrier:slug}/user-carriers',
                'admin/carrier/{carrier:slug}/drivers',
                'admin/carrier/{carrier:slug}/documents'
            ];
            
            $this->newLine();
            $this->info('6. SPECIFIC ROUTE ANALYSIS:');
            
            foreach ($specificRoutes as $routePattern) {
                $found = false;
                foreach ($routes as $route) {
                    if ($route->uri() === $routePattern) {
                        $found = true;
                        $this->line('   - ' . $routePattern . ': REGISTERED');
                        $this->line('     Methods: ' . implode(', ', $route->methods()));
                        $this->line('     Action: ' . $route->getActionName());
                        
                        // Check middleware
                        $routeMiddleware = $route->middleware();
                        $this->line('     Middleware: ' . (empty($routeMiddleware) ? 'None' : implode(', ', $routeMiddleware)));
                        break;
                    }
                }
                
                if (!$found) {
                    $this->error('   - ' . $routePattern . ': NOT FOUND');
                }
            }
            
            $this->newLine();
            $this->info('7. CONTROLLER ANALYSIS:');
            
            // Check if admin controllers exist
            $controllers = [
                'App\\Http\\Controllers\\Admin\\CarrierController',
                'App\\Http\\Controllers\\Admin\\UserCarrierController',
                'App\\Http\\Controllers\\Admin\\UserDriverController',
                'App\\Http\\Controllers\\Admin\\CarrierDocumentController'
            ];
            
            foreach ($controllers as $controller) {
                $exists = class_exists($controller);
                $this->line('   - ' . $controller . ': ' . ($exists ? 'EXISTS' : 'MISSING'));
                
                if ($exists) {
                    $reflection = new ReflectionClass($controller);
                    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
                    $publicMethods = array_filter($methods, function($method) {
                        return !$method->isConstructor() && !$method->isDestructor() && !str_starts_with($method->name, '__');
                    });
                    $methodNames = array_map(function($method) { return $method->name; }, $publicMethods);
                    $this->line('     Public methods: ' . implode(', ', $methodNames));
                }
            }
            
            $this->newLine();
            $this->info('8. BOOTSTRAP CONFIGURATION:');
            
            // Check bootstrap/app.php configuration
            $bootstrapPath = base_path('bootstrap/app.php');
            if (file_exists($bootstrapPath)) {
                $bootstrapContent = file_get_contents($bootstrapPath);
                $hasAdminRoutes = str_contains($bootstrapContent, 'admin');
                $hasAuthMiddleware = str_contains($bootstrapContent, 'auth');
                
                $this->line('   - Bootstrap file exists: Yes');
                $this->line('   - Contains admin configuration: ' . ($hasAdminRoutes ? 'Yes' : 'No'));
                $this->line('   - Contains auth middleware: ' . ($hasAuthMiddleware ? 'Yes' : 'No'));
            } else {
                $this->error('   - Bootstrap file: Missing');
            }
            
            $this->newLine();
            $this->info('9. POTENTIAL ISSUES IDENTIFIED:');
            
            $issues = [];
            
            // Check for common issues
            if (!$carrier) {
                $issues[] = "Carrier 'depeche-mode-llc' does not exist in database";
            }
            
            if (count($adminRoutes) === 0) {
                $issues[] = "No admin carrier routes found - routes may not be loaded";
            }
            
            if (!isset($middlewareGroups['web'])) {
                $issues[] = "Web middleware group not configured";
            }
            
            if (!isset($authMiddleware['auth'])) {
                $issues[] = "Auth middleware not registered";
            }
            
            if (empty($issues)) {
                $this->line('   - No obvious configuration issues detected');
                $this->line('   - Issue likely related to authentication/authorization');
            } else {
                foreach ($issues as $issue) {
                    $this->error('   - ' . $issue);
                }
            }
            
            $this->newLine();
            $this->info('10. RECOMMENDATIONS:');
            
            if (!$carrier) {
                $this->line('   - Create or verify the carrier \'depeche-mode-llc\' exists');
            }
            
            $this->line('   - Ensure user is logged in with proper admin privileges');
            $this->line('   - Check if admin routes require specific permissions');
            $this->line('   - Verify middleware is not redirecting to welcome page');
            $this->line('   - Test with authenticated admin user session');
            $this->line('   - Check if routes are protected by role/permission middleware');
            
        } catch (Exception $e) {
            $this->error('ERROR: ' . $e->getMessage());
            $this->line('Stack trace: ' . $e->getTraceAsString());
        }

        $this->newLine();
        $this->info('=== END OF REPORT ===');
        
        return 0;
    }
}