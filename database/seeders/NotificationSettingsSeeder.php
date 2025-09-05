<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\NotificationSetting;
use Illuminate\Support\Facades\DB;

class NotificationSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar configuraciones existentes
        DB::table('notification_settings')->truncate();
        
        // Configuraciones por defecto para notificaciones de carriers
        $defaultSettings = [
            // Configuraciones para pasos completados
            [
                'event_type' => 'step_completed',
                'step' => 'step1',
                'recipients' => json_encode([
                    'admin@efservices.la',
                    'notifications@efservices.la'
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'event_type' => 'step_completed',
                'step' => 'step2',
                'recipients' => json_encode([
                    'admin@efservices.la',
                    'notifications@efservices.la'
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'event_type' => 'step_completed',
                'step' => 'step3',
                'recipients' => json_encode([
                    'admin@efservices.la',
                    'notifications@efservices.la'
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'event_type' => 'step_completed',
                'step' => 'step4',
                'recipients' => json_encode([
                    'admin@efservices.la',
                    'notifications@efservices.la'
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Configuración para registro completado
            [
                'event_type' => 'registration_completed',
                'step' => null,
                'recipients' => json_encode([
                    'admin@efservices.la',
                    'notifications@efservices.la',
                    'onboarding@efservices.la'
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
        
        // Insertar configuraciones
        foreach ($defaultSettings as $setting) {
            NotificationSetting::create($setting);
        }
        
        $this->command->info('Configuraciones de notificación creadas exitosamente.');
    }
}
