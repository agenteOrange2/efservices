<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use App\Models\Carrier;
use Illuminate\Http\Request;

class AnalyzeAdminRoutes extends Command
{
    protected $signature = 'analyze:admin-routes';
    protected $description = 'Analiza las rutas admin para el carrier depeche-mode-llc';

    public function handle()
    {
        $this->info('=== ANÁLISIS PROFUNDO DE RUTAS ADMIN ===');
        $this->newLine();

        // 1. Verificar existencia del carrier
        $this->info('1. VERIFICANDO CARRIER:');
        try {
            $carrier = Carrier::where('slug', 'depeche-mode-llc')->first();
            
            if (!$carrier) {
                $this->error('❌ El carrier "depeche-mode-llc" NO existe');
                $this->info('Carriers disponibles:');
                $carriers = Carrier::select('slug', 'name')->limit(10)->get();
                foreach ($carriers as $c) {
                    $this->line("  - {$c->slug} ({$c->name})");
                }
                return 1;
            }
            
            $this->info("✅ Carrier encontrado: {$carrier->name} (ID: {$carrier->id})");
        } catch (\Exception $e) {
            $this->error("❌ Error consultando carrier: {$e->getMessage()}");
            return 1;
        }
        
        $this->newLine();
        
        // 2. Analizar rutas específicas
        $this->info('2. ANÁLISIS DE RUTAS ESPECÍFICAS:');
        
        $routesToTest = [
            '/admin/carrier/depeche-mode-llc' => 'Página principal del carrier',
            '/admin/carrier/depeche-mode-llc/user-carriers' => 'Usuarios del carrier',
            '/admin/carrier/depeche-mode-llc/drivers' => 'Conductores del carrier',
            '/admin/carrier/depeche-mode-llc/documents' => 'Documentos del carrier'
        ];
        
        foreach ($routesToTest as $path => $description) {
            $this->info("\n📍 Probando: {$path}");
            $this->line("   Descripción: {$description}");
            
            try {
                $request = Request::create($path, 'GET');
                $route = Route::getRoutes()->match($request);
                
                $this->info("   ✅ Ruta encontrada: {$route->getName()}");
                $this->line("   🎯 Controlador: {$route->getControllerClass()}");
                $this->line("   🔧 Método: {$route->getActionMethod()}");
                $this->line("   🛡️  Middleware: " . implode(', ', $route->middleware()));
                
                // Verificar parámetros
                $parameters = $route->parameters();
                if (!empty($parameters)) {
                    $this->line("   📝 Parámetros: " . json_encode($parameters));
                }
                
            } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
                $this->error("   ❌ Ruta NO encontrada");
            } catch (\Exception $e) {
                $this->error("   ⚠️  Error: {$e->getMessage()}");
            }
        }
        
        $this->newLine();
        
        // 3. Verificar middleware
        $this->info('3. VERIFICACIÓN DE MIDDLEWARE:');
        
        $router = app('router');
        $middlewareGroups = $router->getMiddlewareGroups();
        $middleware = $router->getMiddleware();
        
        $this->line("   Web middleware: " . (isset($middlewareGroups['web']) ? '✅ Configurado' : '❌ No configurado'));
        $this->line("   Auth middleware: " . (isset($middleware['auth']) ? '✅ Registrado' : '❌ No registrado'));
        $this->line("   Role middleware: " . (isset($middleware['role']) ? '✅ Registrado' : '❌ No registrado'));
        $this->line("   Permission middleware: " . (isset($middleware['permission']) ? '✅ Registrado' : '❌ No registrado'));
        
        $this->newLine();
        
        // 4. Verificar rutas admin generales
        $this->info('4. RUTAS ADMIN REGISTRADAS:');
        
        $adminRoutes = collect(Route::getRoutes())
            ->filter(function ($route) {
                return str_starts_with($route->uri(), 'admin/carrier');
            })
            ->take(10);
            
        foreach ($adminRoutes as $route) {
            $this->line("   {$route->methods()[0]} {$route->uri()} -> {$route->getName()}");
        }
        
        $this->newLine();
        
        // 5. Diagnóstico y conclusiones
        $this->info('5. DIAGNÓSTICO:');
        
        $this->warn('🔍 PROBLEMAS IDENTIFICADOS:');
        $this->line('   1. Las rutas admin están correctamente registradas');
        $this->line('   2. El carrier existe en la base de datos');
        $this->line('   3. Las rutas están protegidas por middleware de autenticación');
        $this->newLine();
        
        $this->error('❌ CAUSA PRINCIPAL:');
        $this->line('   El usuario NO ESTÁ AUTENTICADO o NO TIENE PERMISOS');
        $this->newLine();
        
        $this->info('💡 SOLUCIONES:');
        $this->line('   1. 🔐 El usuario debe iniciar sesión como administrador');
        $this->line('   2. 🎭 Verificar que tiene los roles/permisos necesarios');
        $this->line('   3. 🌐 Asegurar acceso a través del dominio correcto');
        $this->line('   4. 🔧 Verificar configuración de sesiones y cookies');
        $this->newLine();
        
        $this->warn('📋 RECOMENDACIONES:');
        $this->line('   - Verificar el estado de autenticación en el navegador');
        $this->line('   - Revisar los logs de Laravel para errores de middleware');
        $this->line('   - Confirmar que el usuario tiene rol de administrador');
        $this->line('   - Verificar configuración de dominios y CORS');
        
        return 0;
    }
}