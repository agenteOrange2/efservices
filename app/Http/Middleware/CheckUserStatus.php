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

            /*
            Log::info('Referral registration route detected', [
                'path' => $request->path(),
                'token' => $request->query('token')
            ]);*/
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

        if ($user && $user->hasRole('driver')) {
            // 1. Validar registro inicial y aplicación
            if (!$user->driverDetails || !$user->driverDetails->carrier_id) {
                return redirect()->route('driver.complete_registration')
                    ->with('warning', 'Please complete your initial registration.');
            }

            // 2. Validar progreso de la aplicación
            if (!$user->driverDetails->application_completed) {
                if (!$request->is('driver/application*')) {
                    $step = $user->driverDetails->current_step ?? 1;
                    return redirect()->route('driver.application.step', ['step' => $step]);
                }
            }

            // 3. Validar estado del driver
            if ($user->driverDetails->status === UserDriverDetail::STATUS_PENDING) {
                if ($request->is('driver/dashboard') || $request->is('driver/pending')) {
                    return $next($request);
                }
                return redirect()->route('driver.pending')
                    ->with('warning', 'Your application is under review.');
            }

            // 4. Validar documentos requeridos
            if (!$user->driverDetails->hasRequiredDocuments()) {
                if ($request->is('driver/documents*')) {
                    return $next($request);
                }
                return redirect()->route('driver.documents.pending')
                    ->with('warning', 'Please upload required documents.');
            }

            // 5. Accesos restringidos
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
        // Extender las rutas públicas para incluir todas las rutas de registro y Livewire
        $publicRoutes = array_merge([
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
            'driver/registration/success',
            // Importante: permitir rutas de Livewire para usuarios no autenticados
            'livewire/*'
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
