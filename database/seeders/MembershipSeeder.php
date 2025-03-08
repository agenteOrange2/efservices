<?php

namespace Database\Seeders;

use App\Models\Membership;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MembershipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        //Definimos los planes
        $plans = [
            [
                'name' => 'Beginner',
                'description' => 'Plan for beginners',
                'max_carrier' => 1,
                'max_drivers' => 5,
                'max_vehicles' => 5,
                'price' => 400, // Adjust price if needed
                'status' => 1, // Active
            ],
            [
                'name' => 'Intermediate',
                'description' => 'Plan for Intermediate',
                'max_carrier' => 2,
                'max_drivers' => 10,
                'max_vehicles' => 10,
                'price' => 600, // Adjust price if needed
                'status' => 1, // Active
            ],
            [
                'name' => 'Pro',
                'description' => 'Plan for Profesionals',
                'max_carrier' => 3,
                'max_drivers' => 15,
                'max_vehicles' => 15,
                'price' => 800, // Adjust price if needed
                'status' => 1, // Active
            ],
        ];

        // Insert the plans into the database
        foreach ($plans as $plan) {
            Membership::updateOrCreate(
                ['name' => $plan['name']], // Check if the plan already exists
                $plan
            );
        }

        $this->command->info('Membership plans seeded successfully.');
    }
}
