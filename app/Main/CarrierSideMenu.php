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
                'icon' => "users",
                'title' => "Drivers Management",
                'sub_menu' => [
                    [
                        'icon' => "user",
                        'route_name' => "carrier.drivers.index",
                        'params' => [],
                        'title' => "List Drivers",
                    ],
                    [
                        'icon' => "userPlus",
                        'route_name' => "carrier.drivers.create",
                        'params' => [],
                        'title' => "Add Driver",
                    ],
                    [
                        'icon' => "alertTriangle",
                        'route_name' => "carrier.drivers.accidents.index",
                        'params' => [],
                        'title' => "Accidents",
                    ],
                    [
                        'icon' => "clipboardCheck",
                        'route_name' => "carrier.drivers.testings.index",
                        'params' => [],
                        'title' => "Drug Tests",
                    ],
                    [
                        'icon' => "clipboardList",
                        'route_name' => "carrier.drivers.inspections.index",
                        'params' => [],
                        'title' => "Inspections",
                    ],
                ],
            ],
            "VEHICLES",
            [
                'icon' => "truck",
                'title' => "Vehicles Management",
                'sub_menu' => [
                    [
                        'icon' => "list",
                        'route_name' => "carrier.vehicles.index",
                        'params' => [],
                        'title' => "List Vehicles",
                    ],
                    [
                        'icon' => "plus",
                        'route_name' => "carrier.vehicles.create",
                        'params' => [],
                        'title' => "Add Vehicle",
                    ],
                ],
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
