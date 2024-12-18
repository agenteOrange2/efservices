<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckSectionAndCarrierAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $section): Response
    {

        $user = $request->user();

        // 1. Verificar permisos de la sección
        if (!$user->can("view_{$section}")) {
            abort(403, "No tienes permiso para acceder a la sección: {$section}");
        }

        // 2. Verificar carriers asignados (si hay un carrier_id en la solicitud)
        if ($request->has('carrier_id')) {
            $carrierId = $request->input('carrier_id');

            if ($user->assignedCarriers()->exists() && !$user->assignedCarriers->contains('id', $carrierId)) {
                abort(403, "No tienes acceso al carrier seleccionado.");
            }
        }
        return $next($request);
    }
}
