<?php

namespace App\View\Composers;

use Illuminate\View\View;
use App\Main\CarrierSideMenu;

class CarrierMenuComposer
{
    public function compose(View $view): void
    {
        if (!is_null(request()->route())) {
            $routeName = request()->route()->getName();
            $activeMenu = $this->activeMenu($routeName);

            $view->with('sideMenu', CarrierSideMenu::menu());
            $view->with('firstLevelActiveIndex', $activeMenu['first_level_active_index']);
            $view->with('secondLevelActiveIndex', $activeMenu['second_level_active_index']);
            $view->with('thirdLevelActiveIndex', $activeMenu['third_level_active_index']);
        }
    }

    public function activeMenu($routeName): array
    {
        $firstLevelActiveIndex = '';
        $secondLevelActiveIndex = '';
        $thirdLevelActiveIndex = '';

        foreach (CarrierSideMenu::menu() as $menuKey => $menu) {
            if (!is_string($menu) && isset($menu['route_name']) && $menu['route_name'] == $routeName) {
                $firstLevelActiveIndex = $menuKey;
            }

            // Resto del código igual que MenuComposer...
        }

        return [
            'first_level_active_index' => $firstLevelActiveIndex,
            'second_level_active_index' => $secondLevelActiveIndex,
            'third_level_active_index' => $thirdLevelActiveIndex
        ];
    }
}