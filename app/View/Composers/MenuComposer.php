<?php

namespace App\View\Composers;

use App\Main\SideMenu;
use Illuminate\View\View;
use App\Main\DriverSideMenu;
use App\Main\CarrierSideMenu;

class MenuComposer
{
    /**
     * Bind menu to the view.
     */

     /*
    public function compose(View $view): void
    {
        if (!is_null(request()->route())) {
            $routeName = request()->route()->getName();
            $activeMenu = $this->activeMenu($routeName);

            $view->with('sideMenu', SideMenu::menu());
            $view->with('firstLevelActiveIndex', $activeMenu['first_level_active_index']);
            $view->with('secondLevelActiveIndex', $activeMenu['second_level_active_index']);
            $view->with('thirdLevelActiveIndex', $activeMenu['third_level_active_index']);
        }
    }
    */
    /**
     * Determine active menu & submenu.
     */

     /*
    public function activeMenu($routeName): array
    {
        $firstLevelActiveIndex = '';
        $secondLevelActiveIndex = '';
        $thirdLevelActiveIndex = '';

        foreach (SideMenu::menu() as $menuKey => $menu) {
            if (!is_string($menu) && isset($menu['route_name']) && $menu['route_name'] == $routeName) {
                $firstLevelActiveIndex = $menuKey;
            }

            if (isset($menu['sub_menu'])) {
                foreach ($menu['sub_menu'] as $subMenuKey => $subMenu) {
                    if (isset($subMenu['route_name']) && $subMenu['route_name'] == $routeName) {
                        $firstLevelActiveIndex = $menuKey;
                        $secondLevelActiveIndex = $subMenuKey;
                    }

                    if (isset($subMenu['sub_menu'])) {
                        foreach ($subMenu['sub_menu'] as $lastSubMenuKey => $lastSubMenu) {
                            if (isset($lastSubMenu['route_name']) && $lastSubMenu['route_name'] == $routeName) {
                                $firstLevelActiveIndex = $menuKey;
                                $secondLevelActiveIndex = $subMenuKey;
                                $thirdLevelActiveIndex = $lastSubMenuKey;
                            }
                        }
                    }
                }
            }
        }

        return [
            'first_level_active_index' => $firstLevelActiveIndex,
            'second_level_active_index' => $secondLevelActiveIndex,
            'third_level_active_index' => $thirdLevelActiveIndex
        ];
    }
        */

        public function compose(View $view): void
        {
            if (!is_null(request()->route())) {
                $routeName = request()->route()->getName();
                
                // Determinar qué menú usar basado en la ruta
                $menu = $this->getAppropriateMenu();
                
                $activeMenu = $this->activeMenu($routeName, $menu);
    
                $view->with('sideMenu', $menu);
                $view->with('firstLevelActiveIndex', $activeMenu['first_level_active_index']);
                $view->with('secondLevelActiveIndex', $activeMenu['second_level_active_index']);
                $view->with('thirdLevelActiveIndex', $activeMenu['third_level_active_index']);
            }
        }
    
        private function getAppropriateMenu()
        {
            // Si la ruta comienza con carrier/, usar el menú del carrier
            if (request()->is('carrier*')) {
                return CarrierSideMenu::menu();
            }

            if (request()->is('driver*')) {
                return DriverSideMenu::menu();
            }    
    
            // Por defecto, usar el menú normal
            return SideMenu::menu();
        }
    
        public function activeMenu($routeName, $menuItems): array
        {
            $firstLevelActiveIndex = '';
            $secondLevelActiveIndex = '';
            $thirdLevelActiveIndex = '';
    
            foreach ($menuItems as $menuKey => $menu) {
                if (!is_string($menu) && isset($menu['route_name']) && $menu['route_name'] == $routeName) {
                    $firstLevelActiveIndex = $menuKey;
                }
    
                if (isset($menu['sub_menu'])) {
                    foreach ($menu['sub_menu'] as $subMenuKey => $subMenu) {
                        if (isset($subMenu['route_name']) && $subMenu['route_name'] == $routeName) {
                            $firstLevelActiveIndex = $menuKey;
                            $secondLevelActiveIndex = $subMenuKey;
                        }
    
                        if (isset($subMenu['sub_menu'])) {
                            foreach ($subMenu['sub_menu'] as $lastSubMenuKey => $lastSubMenu) {
                                if (isset($lastSubMenu['route_name']) && $lastSubMenu['route_name'] == $routeName) {
                                    $firstLevelActiveIndex = $menuKey;
                                    $secondLevelActiveIndex = $subMenuKey;
                                    $thirdLevelActiveIndex = $lastSubMenuKey;
                                }
                            }
                        }
                    }
                }
            }

    
            return [
                'first_level_active_index' => $firstLevelActiveIndex,
                'second_level_active_index' => $secondLevelActiveIndex,
                'third_level_active_index' => $thirdLevelActiveIndex
            ];
        }
}
