<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin\Vehicle\VehicleType;

class VehicleTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            'Sedan',
            'SUV',
            'Truck',
            'Van',
            'Coupe',
            'Hatchback',
            'Convertible',
            'Pickup',
            'Bus',
            'Trailer'
        ];

        foreach ($types as $type) {
            VehicleType::firstOrCreate([
                'name' => $type
            ]);
        }
    }
}