<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class LogoutController extends Controller
{
    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        // Obtener el ID de la sesión actual antes de cerrarla
        $sessionId = $request->session()->getId();
        
        // Cerrar la sesión de autenticación
        Auth::guard('web')->logout();
        
        // Invalidar la sesión actual
        $request->session()->invalidate();
        
        // Regenerar el token de sesión
        $request->session()->regenerateToken();
        
        // Eliminar la sesión de la base de datos directamente
        // Esto es necesario porque a veces Laravel no limpia correctamente las sesiones
        DB::table('sessions')->where('id', $sessionId)->delete();
        
        // Eliminar todas las cookies relacionadas con la sesión
        $cookies = [
            Cookie::forget('laravel_session'),
            Cookie::forget(Auth::getRecallerName()),
            Cookie::forget('XSRF-TOKEN')
        ];
        
        // Redirigir a la página principal con todas las cookies eliminadas
        $response = redirect('/');
        foreach ($cookies as $cookie) {
            $response = $response->withCookie($cookie);
        }
        
        return $response;
    }
}
