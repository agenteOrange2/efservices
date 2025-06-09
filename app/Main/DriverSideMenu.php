<?php

namespace App\Main;
use Illuminate\Support\Facades\Auth;

class DriverSideMenu
{
    public static function menu()
    {

        return [
            "DASHBOARD",
            [
                'icon' => "BookMarked",
                'route_name' => "driver.dashboard",
                'params' => [],
                'title' => "Dashboard",
            ],            

            "ENTRENAMIENTOS",
            [
                'icon' => "graduation-cap",
                'route_name' => "driver.trainings.index",
                'params' => [],
                'title' => "Mis Entrenamientos",
            ],
            
            // ... otros elementos del menú
        ];
    }
}
