<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $requiredRole
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $requiredRole): Response
    {
        // Verificar si es una ruta pública que no requiere autenticación
        if ($this->isPublicRoute($request, $requiredRole)) {
            Log::info('CheckRoleAccess: Public route, allowing access', [
                'path' => $request->path(),
                'required_role' => $requiredRole
            ]);
            return $next($request);
        }

        $user = Auth::user();
        
        if (!$user) {
            Log::info('CheckRoleAccess: No authenticated user, redirecting to login', [
                'path' => $request->path(),
                'required_role' => $requiredRole
            ]);
            return redirect()->route('login');
        }

        Log::info('CheckRoleAccess: Checking role access', [
            'user_id' => $user->id,
            'user_roles' => $user->getRoleNames()->toArray(),
            'required_role' => $requiredRole,
            'path' => $request->path()
        ]);

        // Verificar si el usuario tiene el rol requerido
        if (!$user->hasRole($requiredRole)) {
            Log::warning('CheckRoleAccess: Access denied - insufficient role', [
                'user_id' => $user->id,
                'user_roles' => $user->getRoleNames()->toArray(),
                'required_role' => $requiredRole,
                'path' => $request->path()
            ]);

            // Redirigir según el rol del usuario
            return $this->redirectBasedOnUserRole($user, $request);
        }

        Log::info('CheckRoleAccess: Access granted', [
            'user_id' => $user->id,
            'required_role' => $requiredRole,
            'path' => $request->path()
        ]);

        return $next($request);
    }

    /**
     * Redirigir al usuario según su rol cuando no tiene acceso
     */
    private function redirectBasedOnUserRole($user, Request $request)
    {
        // Si es superadmin pero intenta acceder a área de carrier/driver
        if ($user->hasRole('superadmin')) {
            return redirect()->route('admin.dashboard')
                ->with('warning', 'Please use the admin interface to manage the system.');
        }

        // Si es user_carrier
        if ($user->hasRole('user_carrier')) {
            // Si intenta acceder al admin, redirigir según su estado
            if ($request->is('admin*')) {
                return $this->redirectCarrierBasedOnStatus($user);
            }
            
            // Si intenta acceder al área de driver
            if ($request->is('driver*')) {
                return redirect()->route('carrier.dashboard')
                    ->with('warning', 'Access denied to driver area.');
            }
        }

        // Si es user_driver
        if ($user->hasRole('user_driver')) {
            // Si intenta acceder al admin o carrier
            if ($request->is('admin*') || $request->is('carrier*')) {
                return $this->redirectDriverBasedOnStatus($user);
            }
        }

        // Redirección por defecto
        return redirect()->route('login')
            ->with('error', 'You do not have permission to access this area.');
    }

    /**
     * Redirigir carrier según su estado cuando intenta acceder al admin
     */
    private function redirectCarrierBasedOnStatus($user)
    {
        $carrierDetails = $user->carrierDetails;
        
        if (!$carrierDetails || !$carrierDetails->carrier_id) {
            return redirect()->route('carrier.wizard.step2')
                ->with('warning', 'Please complete your registration first.');
        }

        $carrier = $carrierDetails->carrier;
        
        // Si el carrier está activo, ir al dashboard
        if ($carrier && $carrier->status === \App\Models\Carrier::STATUS_ACTIVE) {
            return redirect()->route('carrier.dashboard')
                ->with('info', 'Welcome to your dashboard.');
        }
        
        // Si está pendiente, ir a pending
        if ($carrier && $carrier->status === \App\Models\Carrier::STATUS_PENDING) {
            return redirect()->route('carrier.pending.validation')
                ->with('info', 'Your account is pending validation.');
        }
        
        // Si está rechazado o inactivo
        return redirect()->route('carrier.confirmation')
            ->with('warning', 'Your account status prevents access to this area.');
    }

    /**
     * Redirigir driver según su estado
     */
    private function redirectDriverBasedOnStatus($user)
    {
        $driverDetails = $user->driverDetails;
        
        if (!$driverDetails) {
            return redirect()->route('driver.complete_registration')
                ->with('warning', 'Please complete your initial registration.');
        }

        $application = $user->driverApplication;
        
        if (!$application) {
            $step = $driverDetails->current_step ?? 1;
            return redirect()->route('driver.registration.continue', ['step' => $step])
                ->with('info', 'Please complete your application.');
        }

        // Redirigir según el estado de la aplicación
        switch ($application->status) {
            case \App\Models\Admin\Driver\DriverApplication::STATUS_DRAFT:
                if (!$driverDetails->application_completed) {
                    $step = $driverDetails->current_step ?? 1;
                    return redirect()->route('driver.registration.continue', ['step' => $step])
                        ->with('info', 'Please complete your application.');
                }
                break;
                
            case \App\Models\Admin\Driver\DriverApplication::STATUS_PENDING:
                return redirect()->route('driver.pending')
                    ->with('info', 'Your application is under review.');
                    
            case \App\Models\Admin\Driver\DriverApplication::STATUS_REJECTED:
                return redirect()->route('driver.rejected')
                    ->with('error', 'Your application has been rejected.');
                    
            case \App\Models\Admin\Driver\DriverApplication::STATUS_APPROVED:
                return redirect()->route('driver.dashboard')
                    ->with('info', 'Welcome to your dashboard.');
        }

        // Por defecto, ir al dashboard del driver
        return redirect()->route('driver.dashboard')
            ->with('info', 'Welcome to your dashboard.');
    }

    /**
     * Verificar si es una ruta pública que no requiere autenticación
     */
    private function isPublicRoute(Request $request, string $requiredRole): bool
    {
        // Rutas públicas para carriers
        if ($requiredRole === 'user_carrier') {
            $publicCarrierRoutes = [
                'carrier/register',
                'carrier/confirm/*',
                'carrier/wizard/step1',
                'carrier/wizard/step2', 
                'carrier/wizard/step3',
                'carrier/wizard/check-uniqueness'
            ];
            
            foreach ($publicCarrierRoutes as $route) {
                if ($request->is($route)) {
                    return true;
                }
            }
        }

        // Rutas públicas para drivers
        if ($requiredRole === 'user_driver') {
            $publicDriverRoutes = [
                'driver/register',
                'driver/register/*',
                'driver/confirm/*',
                'driver/error',
                'driver/quota-exceeded',
                'driver/carrier-status'
            ];
            
            foreach ($publicDriverRoutes as $route) {
                if ($request->is($route)) {
                    return true;
                }
            }
        }

        return false;
    }
}