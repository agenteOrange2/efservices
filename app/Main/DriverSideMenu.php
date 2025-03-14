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

            // ... otros elementos del menú
        ];
    }
}
