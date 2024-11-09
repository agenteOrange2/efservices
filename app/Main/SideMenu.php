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
            [
                'icon' => "UserSquare",
                'route_name' => "admin.users.index",
                'params' => [],
                'title' => "Users",
            ],
            "USER MANAGEMENT",
            [
                'icon' => "UserSquare",
                'route_name' => "admin.users",
                'params' => [],
                'title' => "Users",
            ],
            [
                'icon' => "CakeSlice",
                'route_name' => "admin.departments",
                'params' => [],
                'title' => "Departments",
            ],
            [
                'icon' => "PackagePlus",
                'route_name' => "admin.add-user",
                'params' => [],
                'title' => "Add User",
            ],
            "PERSONAL DASHBOARD",
            [
                'icon' => "Presentation",
                'route_name' => "admin.profile-overview",
                'params' => [],
                'title' => "Profile Overview",
            ],
            [
                'icon' => "CalendarRange",
                'route_name' => "admin.profile-overview-events",
                'params' => [],
                'title' => "Events",
            ],
            [
                'icon' => "Medal",
                'route_name' => "admin.profile-overview-achievements",
                'params' => [],
                'title' => "Achievements",
            ],
            [
                'icon' => "TabletSmartphone",
                'route_name' => "admin.profile-overview-contacts",
                'params' => [],
                'title' => "Contacts",
            ],
            [
                'icon' => "Snail",
                'route_name' => "admin.profile-overview-default",
                'params' => [],
                'title' => "Default",
            ],
            "GENERAL SETTINGS",
            [
                'icon' => "Briefcase",
                'route_name' => "admin.settings",
                'params' => [],
                'title' => "Profile Info",
            ],
            [
                'icon' => "MailCheck",
                'route_name' => "admin.settings-email-settings",
                'params' => [],
                'title' => "Email Settings",
            ],
            [
                'icon' => "Fingerprint",
                'route_name' => "admin.settings-security",
                'params' => [],
                'title' => "Security",
            ],
            [
                'icon' => "Radar",
                'route_name' => "admin.settings-preferences",
                'params' => [],
                'title' => "Preferences",
            ],
            [
                'icon' => "DoorOpen",
                'route_name' => "admin.settings-two-factor-authentication",
                'params' => [],
                'title' => "Two-factor Authentication",
            ],
            [
                'icon' => "Keyboard",
                'route_name' => "admin.settings-device-history",
                'params' => [],
                'title' => "Device History",
            ],
            [
                'icon' => "Ticket",
                'route_name' => "admin.settings-notification-settings",
                'params' => [],
                'title' => "Notification Settings",
            ],
            [
                'icon' => "BusFront",
                'route_name' => "admin.settings-connected-services",
                'params' => [],
                'title' => "Connected Services",
            ],
            [
                'icon' => "Podcast",
                'route_name' => "admin.settings-social-media-links",
                'params' => [],
                'title' => "Social Media Links",
            ],
            [
                'icon' => "PackageX",
                'route_name' => "admin.settings-account-deactivation",
                'params' => [],
                'title' => "Account Deactivation",
            ],
            "ACCOUNT",
            [
                'icon' => "PercentSquare",
                'route_name' => "admin.billing",
                'params' => [],
                'title' => "Billing",
            ],
            [
                'icon' => "DatabaseZap",
                'route_name' => "admin.invoice",
                'params' => [],
                'title' => "Invoice",
            ],
            "E-COMMERCE",
            [
                'icon' => "BookMarked",
                'route_name' => "admin.categories",
                'params' => [],
                'title' => "Categories",
            ],
            [
                'icon' => "Compass",
                'route_name' => "admin.add-product",
                'params' => [],
                'title' => "Add Product",
            ],
            [
                'icon' => "Table2",
                'route_name' => "admin.products",
                'params' => [],
                'title' => "Products",
                'sub_menu' => [
                    [
                        'icon' => "LayoutPanelTop",
                        'route_name' => "admin.product-list",
                        'params' => [],
                        'title' => "Product List",
                    ],
                    [
                        'icon' => "LayoutPanelLeft",
                        'route_name' => "admin.product-grid",
                        'params' => [],
                        'title' => "Product Grid",
                    ],
                ],
            ],
            [
                'icon' => "SigmaSquare",
                'route_name' => "admin.transactions",
                'params' => [],
                'title' => "Transactions",
                'sub_menu' => [
                    [
                        'icon' => "DivideSquare",
                        'route_name' => "admin.transaction-list",
                        'params' => [],
                        'title' => "Transaction List",
                    ],
                    [
                        'icon' => "PlusSquare",
                        'route_name' => "admin.transaction-detail",
                        'params' => [],
                        'title' => "Transaction Detail",
                    ],
                ],
            ],
            [
                'icon' => "FileArchive",
                'route_name' => "admin.sellers",
                'params' => [],
                'title' => "Sellers",
                'sub_menu' => [
                    [
                        'icon' => "FileImage",
                        'route_name' => "admin.seller-list",
                        'params' => [],
                        'title' => "Seller List",
                    ],
                    [
                        'icon' => "FileBox",
                        'route_name' => "admin.seller-detail",
                        'params' => [],
                        'title' => "Seller Detail",
                    ],
                ],
            ],
            [
                'icon' => "Goal",
                'route_name' => "admin.reviews",
                'params' => [],
                'title' => "Reviews",
            ],
            "AUTHENTICATIONS",
            [
                'icon' => "BookKey",
                'route_name' => "admin.login",
                'params' => [],
                'title' => "Login",
            ],
            [
                'icon' => "BookLock",
                'route_name' => "admin.register",
                'params' => [],
                'title' => "Register",
            ],
        ];
    }
}
