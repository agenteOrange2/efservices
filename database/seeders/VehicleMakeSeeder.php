<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin\Vehicle\VehicleMake;

class VehicleMakeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $makes = [
            'Ford',
            'Toyota',
            'Chevrolet',
            'Honda',
            'Nissan',
            'BMW',
            'Mercedes-Benz',
            'Audi',
            'Volkswagen',
            'Hyundai'
        ];

        foreach ($makes as $make) {
            VehicleMake::firstOrCreate([
                'name' => $make
            ]);
        }
    }
}