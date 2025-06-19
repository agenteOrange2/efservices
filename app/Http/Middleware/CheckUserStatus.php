<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Carrier;
use Illuminate\Http\Request;
use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin\Driver\DriverApplication;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        Log::info('CheckUserStatus middleware', [
            'user_id' => $user ? $user->id : null,
            'path' => $request->path(),
            'is_carrier' => $user ? $user->hasRole('user_carrier') : false,
            'is_driver' => $user ? $user->hasRole('driver') : false
        ]);

        // Verificar si es una ruta de registro por referencia
        if ($this->isReferralRoute($request)) {
            return $next($request);
        }

        // Rutas públicas que siempre son accesibles
        $publicRoutes = ['/', 'login', 'carrier/register', 'carrier/confirm/*', 'driver/register', 'driver/confirm/*', 'vehicle-verification/*',  'logout', 'employment-verification/*'];
        if (!$user && !$this->isPublicRoute($request, $publicRoutes)) {
            return redirect()->route('login')
                ->with('warning', 'Please login to continue.');
        }

        // Verificación para User Carrier
        if ($user && $user->hasRole('user_carrier')) {
            
            // Verificar primero si el usuario está activo (independientemente del carrier)
            if ($user->status != 1) { // Si el usuario está inactivo
                Auth::logout(); // Cerrar sesión del usuario
                return redirect()->route('login')
                    ->withErrors(['email' => 'Your account has been deactivated. Please contact support.']);
            }

            // Verificar estado del carrier y redirigir según corresponda
            if (!$this->isCarrierSetupRoute($request)) {
                // Agregamos logs para diagnosticar el problema
                Log::info('Middleware check', [
                    'user_id' => $user->id,
                    'has_carrier_details' => $user->carrierDetails ? 'yes' : 'no',
                    'carrier_id' => $user->carrierDetails ? $user->carrierDetails->carrier_id : null,
                    'path' => $request->path()
                ]);
                
                // PRIMERO: verificar si el usuario tiene que completar su registro
                if (!$user->carrierDetails || !$user->carrierDetails->carrier_id) {
                    Log::info('Redirigiendo a complete-registration', ['user_id' => $user->id]);
                    return redirect()->route('carrier.complete_registration')
                        ->with('warning', 'Please complete your registration first.');
                }

                // SEGUNDO: Verificar estado del user_carrier
                if ($user->carrierDetails->status != 1) { // Asumiendo que 1 es STATUS_ACTIVE
                    Log::info('Redirigiendo a pending (user_carrier inactive)', ['user_id' => $user->id]);
                    return redirect()->route('carrier.pending')
                        ->with('warning', 'Your user account is pending approval.');
                }
                
                // TERCERO: Verificar estado del carrier
                $carrier = $user->carrierDetails->carrier;
                Log::info('Verificando carrier status', [
                    'user_id' => $user->id,
                    'carrier_id' => $carrier->id,
                    'carrier_status' => $carrier->status,
                    'ACTIVE_STATUS' => Carrier::STATUS_ACTIVE
                ]);

                // Si el carrier está pendiente o inactivo y NO está en la ruta de documentos o logout
                if ($carrier->status !== Carrier::STATUS_ACTIVE && !$request->is('carrier/*/documents*') && !$request->is('carrier/confirmation') && !$request->is('logout')) {
                    Log::info('Redirigiendo a confirmation (carrier not active)', [
                        'user_id' => $user->id,
                        'carrier_status' => $carrier->status
                    ]);
                    return redirect()->route('carrier.confirmation')
                        ->with('warning', 'Your carrier account is pending approval.');
                }

                // Si necesita subir documentos y no está en la ruta de documentos
                if ($carrier->document_status === 'in_progress' && !$request->is('carrier/*/documents*')) {
                    return redirect()->route('carrier.documents.index', $carrier->slug)
                        ->with('warning', 'Please complete your document submission before proceeding.');
                }
            }

            // Prevenir acceso al área de admin
            if ($request->is('admin*')) {
                return redirect()->route('carrier.dashboard')
                    ->with('warning', 'Access denied to admin area.');
            }
        }

        if ($user && $user->hasRole('driver')) {
            // 1. Verificar si existe el detalle del driver
            if (!$user->driverDetails) {
                return redirect()->route('driver.complete_registration')
                    ->with('warning', 'Please complete your initial registration.');
            }

            $driverDetail = $user->driverDetails;

            // 2. Obtener la aplicación del driver
            $application = $user->driverApplication ?? null;

            // Si no tiene aplicación, crearla en estado borrador
            if (!$application) {
                $application = DriverApplication::create([
                    'user_id' => $user->id,
                    'status' => DriverApplication::STATUS_DRAFT
                ]);
                Log::info('Created new driver application', ['user_id' => $user->id, 'application_id' => $application->id]);
            }

            // 3. Lógica según el estado de la aplicación
            switch ($application->status) {
                case DriverApplication::STATUS_DRAFT:
                    // Si la aplicación no está completa y no está en ninguna ruta relacionada con el registro
                    if (
                        !$driverDetail->application_completed &&
                        !$request->is('driver/registration*') &&
                        !$request->is('livewire/*')
                    ) {

                        $step = $driverDetail->current_step ?? 1;
                        return redirect()->route('driver.registration.continue', ['step' => $step])
                            ->with('info', 'Please complete your application to continue.');
                    }
                    break;

                case DriverApplication::STATUS_PENDING:
                    // Si la aplicación está pendiente de revisión
                    if (!$request->is('driver/pending') && !$this->isDriverExemptRoute($request)) {
                        return redirect()->route('driver.pending')
                            ->with('warning', 'Your application is under review.');
                    }
                    break;

                case DriverApplication::STATUS_REJECTED:
                    // Si la aplicación fue rechazada
                    if (!$request->is('driver/rejected') && !$this->isDriverExemptRoute($request)) {
                        return redirect()->route('driver.rejected')
                            ->with('error', 'Your application has been rejected. Please contact support for more information.');
                    }
                    break;

                case DriverApplication::STATUS_APPROVED:
                    // Si la aplicación está aprobada, verificar documentos
                    if (!$driverDetail->hasRequiredDocuments() && !$request->is('driver/documents*') && !$this->isDriverExemptRoute($request)) {
                        return redirect()->route('driver.documents.pending')
                            ->with('warning', 'Please upload required documents.');
                    }
                    break;
            }

            // 4. Accesos restringidos para todos los drivers
            if ($request->is('admin*') || $request->is('carrier*')) {
                return redirect()->route('driver.dashboard')
                    ->with('warning', 'Access denied to this area.');
            }
        }

        // Verificación para SuperAdmin
        if ($user && $user->hasRole('superadmin')) {
            // El superadmin puede acceder a todas las rutas admin
            if ($request->is('driver*') || $request->is('carrier/dashboard*')) {
                return redirect()->route('admin.dashboard')
                    ->with('warning', 'Please use the admin interface to manage drivers and carriers.');
            }
            
            // Verificar si intenta acceder a rutas de administración
            if ($request->is('admin*') && !$user->can('view admin dashboard')) {
                return redirect()->route('login')
                    ->with('error', 'You do not have permission to access the admin dashboard.');
            }
        }

        return $next($request);
    }

    private function isPublicRoute(Request $request): bool
    {
        // Rutas públicas que siempre son accesibles
        $publicRoutes = [
            '/',
            'login',
            'carrier/register',
            'carrier/confirm/*',
            'driver/register',
            'driver/register/form',
            'driver/confirm/*',
            'driver/*',
            'driver/error',
            'driver/quota-exceeded',
            'driver/carrier-status',
            'driver/pending',
            'driver/rejected',
            'driver/registration/success',
            'livewire/*',
            'vehicle-verification/*',
            'employment-verification/*',

        ];

        foreach ($publicRoutes as $route) {
            if ($request->is($route)) {
                return true;
            }
        }

        return false;
    }

    private function isReferralRoute(Request $request): bool
    {
        // Solo verifica que sea la ruta de registro con token
        if ($request->is('driver/register/*') && $request->has('token')) {
            return true;
        }
        return false;
    }

    private function isCarrierSetupRoute(Request $request): bool
    {
        // Si viene de complete-registration o va hacia confirmation, permitir sin restricciones
        if ($request->is('carrier/complete-registration') || $request->is('carrier/confirmation')) {
            Log::info('Ruta permitida sin restricciones: ' . $request->path());
            return true;
        }
        
        $setupRoutes = [
            'carrier/complete-registration',
            'carrier/confirmation',
            'carrier/pending', 
            'carrier/register',
            'carrier/confirm/*',
            'carrier/*/documents*'
        ];
        
        // Rutas que definitivamente NO son de configuración
        $nonSetupRoutes = [
            'carrier/dashboard',
            'carrier/profile',
            'carrier/load/*'
        ];
        
        // Si la ruta está en la lista de NO configuración, return false inmediatamente
        foreach ($nonSetupRoutes as $route) {
            if ($request->is($route)) {
                return false;
            }
        }

        return $this->routeMatches($request, $setupRoutes);
    }

    private function isDriverExemptRoute(Request $request): bool
    {
        // Rutas que un driver puede acceder aunque su aplicación no esté aprobada
        $exemptRoutes = [
            'driver/logout',
            'driver/profile',
            'driver/account',
            'driver/select-carrier',
            'driver/registration/*',
            'driver/pending',
            'driver/rejected',
            'driver/documents/*'
        ];

        foreach ($exemptRoutes as $route) {
            if ($request->is($route)) {
                return true;
            }
        }
        return false;
    }

    private function routeMatches(Request $request, array $routes): bool
    {
        $path = $request->path();
        
        // Log para depuración
        Log::info('Verificando ruta en routeMatches', [
            'path' => $path,
            'routes' => $routes
        ]);
        
        // Verificación especial para la ruta de confirmación
        if ($path === 'carrier/confirmation') {
            Log::info('Ruta de confirmación detectada, permitiendo acceso');
            return true;
        }
        
        foreach ($routes as $route) {
            if ($request->is($route)) {
                Log::info('Ruta coincide con patrón: ' . $route);
                return true;
            }
        }
        
        return false;
    }
}
