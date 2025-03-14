<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Barryvdh\DomPDF\Facade\PDF; 

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
        session(['activeTheme' => 'raze']);

        PDF::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'tempDir' => storage_path('app/temp'),
            'chroot' => [
                public_path(),
                storage_path('app'),
                storage_path('app/public'),
                storage_path('app/temp')
            ]
        ]);
    }
}
