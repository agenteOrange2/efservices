<?php

namespace App\Providers;

use App\Models\Carrier;
use App\Models\CarrierDocument;
use App\Models\Admin\Vehicle\Vehicle;
use App\Observers\CarrierObserver;
use App\Observers\CarrierDocumentObserver;
// use App\Observers\DriverObserver; // Comentado: Driver model no existe
// use App\Observers\VehicleObserver; // Comentado: usar Vehicle específico si es necesario
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
        // Registrar observers para invalidación automática de caché
        Carrier::observe(CarrierObserver::class);
        CarrierDocument::observe(CarrierDocumentObserver::class);
        // Driver::observe(DriverObserver::class); // Comentado: Driver model no existe
        // Vehicle::observe(VehicleObserver::class); // Comentado: usar Vehicle específico si es necesario
        
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
