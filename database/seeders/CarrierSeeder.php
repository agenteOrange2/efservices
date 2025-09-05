<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Carrier;
use App\Models\Membership;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use App\Models\UserCarrierDetail;
use Illuminate\Support\Facades\Hash;

class CarrierSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create();
        
        // Obtener un plan de membresía o crear uno por defecto si no existe
        $membership = Membership::first() ?? Membership::create([
            'name' => 'Basic Plan',
            'description' => 'Basic membership plan',
            'price' => 399.99,
            'max_carrier' => 5,
            'max_drivers' => 10,
            'max_vehicles' => 10,
            'status' => 1,
        ]);

        // Array de estados de USA para más realismo
        $states = [
            'AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'FL', 'GA',
            'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD',
            'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ',
            'NM', 'NY', 'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC',
            'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA', 'WV', 'WI', 'WY'
        ];

        // Documentos estados permitidos
        $documentStatuses = ['pending', 'in_progress', 'skipped'];

        // Crear 10 carriers con datos realistas
        for ($i = 0; $i < 10; $i++) {
            $companyName = $faker->company;
            $carrier = Carrier::create([
                'name' => $companyName,
                'slug' => Str::slug($companyName),
                'address' => $faker->streetAddress,
                'state' => $faker->randomElement($states),
                'zipcode' => $faker->postcode,
                'ein_number' => $faker->numerify('##-#######'),
                'dot_number' => $faker->numerify('########'),
                'mc_number' => $faker->numerify('MC######'),
                'state_dot' => $faker->numerify('ST####'),
                'ifta_account' => $faker->numerify('IFTA####'),
                'id_plan' => $membership->id,
                'status' => $faker->randomElement([
                    Carrier::STATUS_ACTIVE,
                    Carrier::STATUS_PENDING,
                    Carrier::STATUS_INACTIVE
                ]),
                'document_status' => $faker->randomElement($documentStatuses),
                'referrer_token' => Str::random(16),
            ]);

            // Crear 2-5 usuarios por carrier
            $numUsers = rand(2, 5);
            for ($j = 0; $j < $numUsers; $j++) {
                $user = User::create([
                    'name' => $faker->name,
                    'email' => $faker->unique()->safeEmail,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'status' => true,
                ]);

                // Asignar rol de user_carrier
                $user->assignRole('user_carrier');

                // Crear detalles del usuario carrier
                UserCarrierDetail::create([
                    'user_id' => $user->id,
                    'carrier_id' => $carrier->id,
                    'phone' => $faker->phoneNumber,
                    'job_position' => $faker->jobTitle,
                    'status' => $faker->randomElement([0, 1, 2]),
                ]);
            }
        }

        $this->command->info('Carriers seeded successfully with associated users!');
    }
}