<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;

class PermissionServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Registrar los servicios necesarios
    }

    public function boot()
    {
        // Registramos el middleware como un cierre en Laravel 11
        Route::aliasMiddleware('role', function ($request, $next, $role) {
            $roles = is_array($role) ? $role : explode('|', $role);
            
            if (!$request->user() || !$request->user()->hasAnyRole($roles)) {
                abort(403, 'No tienes permiso para acceder a esta página');
            }
            
            return $next($request);
        });
        
        Route::aliasMiddleware('permission', function ($request, $next, $permission) {
            $permissions = is_array($permission) ? $permission : explode('|', $permission);
            
            if (!$request->user() || !$request->user()->hasAnyPermission($permissions)) {
                abort(403, 'No tienes permiso para acceder a esta página');
            }
            
            return $next($request);
        });
        
        Route::aliasMiddleware('role_or_permission', function ($request, $next, $roleOrPermission) {
            $rolesOrPermissions = is_array($roleOrPermission) 
                ? $roleOrPermission 
                : explode('|', $roleOrPermission);
            
            if (!$request->user() || 
                (!$request->user()->hasAnyRole($rolesOrPermissions) && 
                 !$request->user()->hasAnyPermission($rolesOrPermissions))) {
                abort(403, 'No tienes permiso para acceder a esta página');
            }
            
            return $next($request);
        });
    }
}
