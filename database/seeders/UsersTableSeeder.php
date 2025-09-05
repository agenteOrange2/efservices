<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Usamos Faker para generar datos aleatorios
        $faker = \Faker\Factory::create();

        // Creamos 10 usuarios
        
        for($i = 0; $i < 5; $i++){
            User::create([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('password'), // Usar un hash para la contraseña                
                'created_at' => Carbon::now()->subDays(rand(1, 100)), // Fecha aleatoria dentro de los últimos 100 días
                'updated_at' => Carbon::now(),
            ]);            
        }
    }
}
