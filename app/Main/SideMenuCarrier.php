<?php

namespace App\Main;

class SideMenu
{
    /**
     * List of side menu items.
     */
    public static function menu(): array
    {
        return [
            "DASHBOARD",
            [
                'icon' => "BookMarked",
                'route_name' => "admin.dashboard",
                'params' => [],
                'title' => "Dashboard",
            ],
        ];
    }
}