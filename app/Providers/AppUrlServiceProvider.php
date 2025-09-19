<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;

class AppUrlServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Solo aplicar la detección automática si estamos en entorno local
        if (app()->environment('local')) {
            $this->configureLocalAppUrl();
        }
    }

    /**
     * Configurar APP_URL automáticamente para entorno local
     */
    private function configureLocalAppUrl(): void
    {
        $currentUrl = $this->detectCurrentUrl();
        
        if ($currentUrl) {
            // Actualizar la configuración de APP_URL
            Config::set('app.url', $currentUrl);
            
            // Forzar que Laravel use esta URL
            URL::forceRootUrl($currentUrl);
            
            // Log para depuración
            /*
            \Illuminate\Support\Facades\Log::info('AppUrlServiceProvider: URL detectada automáticamente', [
                'detected_url' => $currentUrl,
                'original_app_url' => env('APP_URL')
            ]);
            */
        }
    }

    /**
     * Detectar la URL actual basada en el contexto de la solicitud
     */
    private function detectCurrentUrl(): ?string
    {
        // Si estamos en una solicitud web, usar los headers HTTP
        if (request()->hasHeader('host')) {
            $host = request()->getHost();
            $scheme = request()->getScheme();
            $port = request()->getPort();
            
            // Construir la URL base
            $url = $scheme . '://' . $host;
            
            // Agregar puerto si no es el estándar
            if (($scheme === 'http' && $port !== 80) || ($scheme === 'https' && $port !== 443)) {
                $url .= ':' . $port;
            }
            
            return $url;
        }
        
        // Si estamos en CLI o no hay solicitud HTTP, intentar detectar desde SERVER
        if (isset($_SERVER['HTTP_HOST'])) {
            $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            return $scheme . '://' . $host;
        }
        
        // Fallback: usar la configuración original
        return env('APP_URL');
    }

    /**
     * Verificar si la URL detectada es válida
     */
    private function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}