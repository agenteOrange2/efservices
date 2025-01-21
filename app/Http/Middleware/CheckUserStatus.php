<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
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
            Log::info('Referral registration route detected', [
                'path' => $request->path(),
                'token' => $request->query('token')
            ]);
            return $next($request);
        }

        // Rutas públicas que siempre son accesibles
        $publicRoutes = ['/', 'login', 'carrier/register', 'carrier/confirm/*', 'driver/register', 'driver/confirm/*'];
        if (!$user && !$this->isPublicRoute($request, $publicRoutes)) {
            return redirect()->route('login')
                ->with('warning', 'Please login to continue.');
        }

        // Verificación para User Carrier
        if ($user && $user->hasRole('user_carrier')) {
            // Verificar estado del carrier y redirigir según corresponda
            if (!$this->isCarrierSetupRoute($request)) {
                if (!$user->carrierDetails || !$user->carrierDetails->carrier_id) {
                    return redirect()->route('carrier.complete_registration')
                        ->with('warning', 'Please complete your registration first.');
                }

                $carrier = $user->carrierDetails->carrier;

                // Si el carrier está pendiente o inactivo y NO está en la ruta de documentos
                if ($carrier->status !== Carrier::STATUS_ACTIVE && !$request->is('carrier/*/documents*')) {
                    return redirect()->route('carrier.confirmation')
                        ->with('warning', 'Your account is pending approval.');
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

        // Verificación para Drivers
        if ($user && $user->hasRole('driver')) {
            // Si el driver no ha completado su registro
            if (!$user->driverDetails || !$user->driverDetails->carrier_id) {
                return redirect()->route('driver.complete_registration')
                    ->with('warning', 'Please complete your driver registration.');
            }

            // Si el driver está pendiente o inactivo
            if ($user->driverDetails->status !== 1) { // Asumiendo 1 como STATUS_ACTIVE
                return redirect()->route('driver.pending')
                    ->with('warning', 'Your driver account is pending approval.');
            }

            // Prevenir acceso a áreas no autorizadas
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
        }

        return $next($request);
    }

    private function isPublicRoute(Request $request, array $publicRoutes): bool
    {
        // Extender las rutas públicas
        $publicRoutes = array_merge([
            '/',
            'login',
            'carrier/register',
            'carrier/confirm/*',
            'driver/register',
            'driver/register/*', // Agregar esta ruta para registro por referencia
            'driver/confirm/*',
            'driver/error',           // Agregar ruta de error
            'driver/quota-exceeded',  // Agregar ruta de cuota excedida
            'driver/carrier-status',  // Agregar la nueva ruta
        ], $publicRoutes);
    
        foreach ($publicRoutes as $route) {
            if ($request->is($route)) {
                Log::info('Public route matched', [
                    'route' => $route,
                    'path' => $request->path()
                ]);
                return true;
            }
        }
    
        Log::info('Route not matched as public', [
            'path' => $request->path(),
            'available_routes' => $publicRoutes
        ]);
    
        return false;
    }
    
    private function isReferralRoute(Request $request): bool
{
    // Verificar si es una ruta de registro por referencia (con token)
    $referralRoutes = [
        'driver/register/*', // Para rutas como driver/register/{token}
        'carrier/*/driver/register', // Para rutas como carrier/{carrier}/driver/register
    ];

    // Verificar si la ruta actual coincide con alguna de las rutas de referencia
    $isReferralPath = $this->routeMatches($request, $referralRoutes);

    // Verificar si hay un token de referencia en la query string
    $hasReferrerToken = $request->has('token') || $request->has('ref');

    Log::info('Checking referral route', [
        'path' => $request->path(),
        'is_referral_path' => $isReferralPath,
        'has_token' => $hasReferrerToken,
        'query_params' => $request->query()
    ]);

    return $isReferralPath || $hasReferrerToken;
}

    private function isCarrierSetupRoute(Request $request): bool
    {
        $setupRoutes = [
            'carrier/complete-registration',
            'carrier/confirmation',
            'carrier/register',
            'carrier/confirm/*',
            'carrier/*/documents*'
        ];

        return $this->routeMatches($request, $setupRoutes);
    }

    private function isDriverSetupRoute(Request $request): bool
    {
        $setupRoutes = [
            'driver/complete-registration',
            'driver/confirmation',
            'driver/register',
            'driver/confirm/*',
            'driver/*/documents*'
        ];

        return $this->routeMatches($request, $setupRoutes);
    }

    private function routeMatches(Request $request, array $routes): bool
    {
        foreach ($routes as $route) {
            if ($request->is($route)) {
                return true;
            }
        }
        return false;
    }
}
