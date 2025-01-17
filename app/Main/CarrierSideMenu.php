<?php

namespace App\Main;

class CarrierSideMenu
{
    public static function menu()
    {
        return [
            'dashboard' => [
                'icon' => 'Home',
                'title' => 'Dashboard',
                'route_name' => 'carrier.dashboard',
                'params' => [],
            ],
            'documents' => [
                'icon' => 'FileText',
                'title' => 'Documents',
                'route_name' => 'carrier.documents.index',
                'params' => [],
            ],
            // ... otros elementos del menú
        ];
    }
}