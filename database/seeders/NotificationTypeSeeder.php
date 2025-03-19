<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\NotificationType;

class NotificationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // $types = [
        //     [
        //         'name' => 'new_user_registration',
        //         'description' => 'Notificación cuando se registra un nuevo usuario'
        //     ],
        //     [
        //         'name' => 'new_carrier_registration',
        //         'description' => 'Notificación cuando se registra un nuevo carrier'
        //     ],
        //     [
        //         'name' => 'document_uploaded',
        //         'description' => 'Notificación cuando se sube un nuevo documento'
        //     ]
        //     // ... otros tipos que necesites
        // ];

        // foreach ($types as $type) {
        //     NotificationType::updateOrCreate(
        //         ['name' => $type['name']],
        //         $type
        //     );
        // }
    }
}
