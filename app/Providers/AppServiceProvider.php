<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Usar las funciones globales en Laravel 11
        app('session')->put('activeTheme', 'raze');
        
        // En Laravel 11, verificamos si el paquete está instalado antes de usarlo
        if (class_exists('Barryvdh\\DomPDF\\ServiceProvider')) {
            // Intentar obtener la instancia de PDF si está disponible
            try {
                $pdf = app('dompdf.wrapper');
                $pdf->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'tempDir' => app('path.storage').'/app/temp',
                    'chroot' => [
                        app('path.public'),
                        app('path.storage').'/app',
                        app('path.storage').'/app/public',
                        app('path.storage').'/app/temp'
                    ]
                ]);
            } catch (\Exception $e) {
                // El servicio no está disponible, no hacemos nada
            }
        }
    }
}
