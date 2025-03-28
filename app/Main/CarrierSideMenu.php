<?php

namespace App\Main;
use Illuminate\Support\Facades\Auth;

class CarrierSideMenu
{
    public static function menu()
    {
        // Verificar si hay un usuario autenticado y tiene carrierDetails
        if (Auth::check() && Auth::user()->carrierDetails) {
            $carrier = Auth::user()->carrierDetails->carrier;
        }

        return [
            "DASHBOARD",
            [
                'icon' => "BookMarked",
                'route_name' => "carrier.dashboard",
                'params' => [],
                'title' => "Dashboard",
            ],            
            "DRIVERS",
            [
                'icon' => "user", // o "userCircle" si prefieres
                'route_name' => "carrier.drivers.index",
                'params' => [],
                'title' => "List Drivers",
            ],
            "CARRIER PROFILE",
            [
                'icon' => "user", // o "userCircle" si prefieres
                'route_name' => "carrier.profile",
                'params' => [],
                'title' => "My Profile",
            ],
            // ... otros elementos del menú
        ];
    }
}
