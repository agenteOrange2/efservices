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
            'is_carrier' => $user ? $user->hasRole('user_carrier') : false
        ]);
    
        // Rutas públicas que siempre son accesibles
        $publicRoutes = ['/', 'login', 'carrier/register', 'carrier/confirm/*'];
        if (!$user && !$this->isPublicRoute($request, $publicRoutes)) {
            return redirect()->route('login')
                ->with('warning', 'Please login to continue.');
        }
    
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
    
        // Restricción para drivers
        if ($user && $user->hasRole('driver') && $request->is('admin*')) {
            return redirect()->route('driver.dashboard')
                ->with('warning', 'Access denied to admin area.');
        }
    
        return $next($request);
    }

    private function isPublicRoute(Request $request, array $publicRoutes): bool
    {
        foreach ($publicRoutes as $route) {
            if ($request->is($route)) {
                return true;
            }
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
            'carrier/*/documents*'  // Añadimos la ruta de documentos como parte del setup
        ];

        foreach ($setupRoutes as $route) {
            if ($request->is($route)) {
                return true;
            }
        }
        return false;
    }
}