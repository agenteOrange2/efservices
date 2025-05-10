<?php

namespace App\Fakers;

use Illuminate\Support\Collection;
use App\Fakers\Products;

class Ecommerce
{
    public static function fakePerformanceInsights(): Collection
    {
        return collect([
            [
                "title" => "Top 20 Products",
                "subtitle" => "Featured products",
                "icon" => "Zap",
                "images" => Products::fakeProducts()[0]['images'],
                "link" => "Explore Products",
            ],
            [
                "title" => "Top 10 Categories",
                "subtitle" => "Popular categories",
                "icon" => "Laptop",
                "images" => Products::fakeProducts()[0]['images'],
                "link" => "Explore Categories",
            ],

            [
                "title" => "Customer Favorites",
                "subtitle" => "Customer of the month",
                "icon" => "Fingerprint",
                "images" => Products::fakeProducts()[0]['images'],
                "link" => "Explore Products",
            ],
            [
                "title" => "Low Stock Alerts",
                "subtitle" => "Items running out",
                "icon" => "Inbox",
                "images" => Products::fakeProducts()[0]['images'],
                "link" => "View Inventory",
            ],

            [
                "title" => "Marketing Highlights",
                "subtitle" => "Recent campaigns",
                "icon" => "Database",
                "images" => Products::fakeProducts()[0]['images'],
                "link" => "Explore Campaigns",
            ],
        ])->shuffle();
    }
}
