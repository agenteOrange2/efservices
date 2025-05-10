<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        if (Auth::guest()) {
            abort(403, 'Unauthorized action.');
        }
        
        // Log de depuración
        Log::info('CheckPermission middleware', [
            'user_id' => Auth::id(),
            'permission' => $permission,
            'path' => $request->path()
        ]);
        
        // Verificar si el usuario es un superadmin o tiene el permiso específico
        // Uso directo de SQL en lugar del método hasPermissionTo, que podría estar dando problemas
        $userId = Auth::id();
        $permissionNames = is_array($permission) ? $permission : explode('|', $permission);
        
        // Verificar si el usuario es superadmin
        $isSuperAdmin = User::find($userId)->roles()->where('name', 'super-admin')->exists();
        
        if ($isSuperAdmin) {
            return $next($request);
        }
        
        // Verificar permisos directamente a través de las tablas de base de datos
        $hasPermission = false;
        
        foreach ($permissionNames as $permName) {
            // Comprobar si el usuario tiene el permiso directamente o a través de un rol
            $hasDirectPermission = User::find($userId)
                ->permissions()
                ->where('name', $permName)
                ->exists();
                
            $hasRolePermission = User::find($userId)
                ->roles()
                ->whereHas('permissions', function ($query) use ($permName) {
                    $query->where('name', $permName);
                })
                ->exists();
                
            if ($hasDirectPermission || $hasRolePermission) {
                $hasPermission = true;
                break;
            }
        }
        
        if ($hasPermission) {
            return $next($request);
        }
        
        Log::warning('Acceso denegado a usuario', [
            'user_id' => $userId,
            'requested_permissions' => $permissionNames,
            'path' => $request->path()
        ]);
        
        abort(403, 'No tienes permiso para acceder a esta página.');
    }
}
