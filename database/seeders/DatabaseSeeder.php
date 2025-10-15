<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Laravel\Jetstream\Rules\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call(UsersTableSeeder::class);
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(MembershipSeeder::class);
        $this->call(DocumentTypeSeeder::class);
        $this->call(CarrierSeeder::class);
        $this->call(NotificationTypeSeeder::class);
        $this->call(LicenseEndorsementSeeder::class);
        $this->call(VehicleMakeSeeder::class);
        $this->call(VehicleTypeSeeder::class);
        $this->call(UserDriverSeeder::class);
        
        $frontendUser = \App\Models\User::factory()->create([
            'name' => 'Elliot Alderson',
            'email' => 'frontend@kuiraweb.com',
            'password' => bcrypt('Admin2025+?'),
        ]);  
                // Asignar el rol de superadmin al usuario
        $frontendUser->assignRole('superadmin');
    }
}
