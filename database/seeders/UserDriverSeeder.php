<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserDriverDetail;
use App\Models\Carrier;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Driver\DriverApplicationDetail;
use App\Models\Admin\Driver\DriverLicense;
use App\Models\Admin\Driver\DriverMedicalQualification;
use App\Models\Admin\Driver\DriverExperience;
use App\Models\Admin\Driver\DriverAddress;
use App\Models\VehicleDriverAssignment;
use Spatie\Permission\Models\Role;
use Faker\Factory as Faker;
use Carbon\Carbon;

class UserDriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Obtener todos los carriers existentes
        $carriers = Carrier::all();
        
        if ($carriers->isEmpty()) {
            $this->command->error('No hay carriers disponibles. Ejecuta CarrierSeeder primero.');
            return;
        }
        
        // Obtener o crear el rol 'user_driver'
        $driverRole = Role::firstOrCreate(['name' => 'user_driver']);
        
        $totalDrivers = 20;
        $driversCreated = 0;
        
        // Nombres comunes americanos para mayor realismo
        $firstNames = [
            'James', 'John', 'Robert', 'Michael', 'William', 'David', 'Richard', 'Joseph', 'Thomas', 'Christopher',
            'Daniel', 'Matthew', 'Anthony', 'Mark', 'Donald', 'Steven', 'Paul', 'Andrew', 'Joshua', 'Kenneth',
            'Kevin', 'Brian', 'George', 'Timothy', 'Ronald', 'Jason', 'Edward', 'Jeffrey', 'Ryan', 'Jacob'
        ];
        
        $lastNames = [
            'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez',
            'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin',
            'Lee', 'Perez', 'Thompson', 'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson'
        ];
        
        // Estados americanos para licencias
        $states = [
            'AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'FL', 'GA',
            'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD',
            'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ',
            'NM', 'NY', 'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC',
            'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA', 'WV', 'WI', 'WY'
        ];
        
        // Tipos de equipo para experiencia
        $equipmentTypes = [
            'Dry Van', 'Refrigerated', 'Flatbed', 'Tanker', 'Container', 
            'Heavy Haul', 'Car Carrier', 'Livestock', 'Dump Truck', 'Box Truck'
        ];
        
        // Posiciones de aplicación
        $positions = ['company_driver', 'owner_operator', 'third_party_driver'];
        
        // Estados de aplicación con distribución específica
        $applicationStatuses = [
            'draft' => 5,      // 5 conductores en borrador
            'pending' => 5,    // 5 conductores pendientes
            'approved' => 5,   // 5 conductores aprobados
            'rejected' => 5    // 5 conductores rechazados
        ];
        
        $statusIndex = 0;
        $currentStatusCount = 0;
        $currentStatus = 'draft';
        
        for ($i = 0; $i < $totalDrivers; $i++) {
            // Determinar el estado de la aplicación según la distribución
            if ($currentStatusCount >= $applicationStatuses[$currentStatus]) {
                $statusIndex++;
                $currentStatusCount = 0;
                $currentStatus = array_keys($applicationStatuses)[$statusIndex] ?? 'draft';
            }
            $currentStatusCount++;
            
            // Crear usuario
            $firstName = $faker->firstName;
            $lastName = $faker->lastName;
            $middleName = $faker->optional(0.7)->firstName;
            
            $fullName = $firstName . ($middleName ? ' ' . $middleName : '') . ' ' . $lastName;
            
            $user = User::create([
                'name' => $fullName,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('password123'),
                'status' => true,
                'access_type' => 'full',
                'email_verified_at' => now(),
            ]);
            
            // Asignar rol de conductor
            $user->assignRole($driverRole);
            
            // Seleccionar carrier aleatorio
            $carrier = $carriers->random();
            
            // Generar fecha de nacimiento (21-65 años)
            $dateOfBirth = $faker->dateTimeBetween('-65 years', '-21 years');
            
            // Generar teléfono válido americano
            $phone = $faker->numerify('###-###-####');
            
            // Determinar el estado del UserDriverDetail basado en el estado de la aplicación
            $userDriverStatus = match($currentStatus) {
                'draft' => 0, // STATUS_INACTIVE
                'pending' => 2, // STATUS_PENDING
                'approved' => 1, // STATUS_ACTIVE
                'rejected' => 0, // STATUS_INACTIVE
            };
            
            // Determinar si acepta términos (más probable si no es draft)
            $termsAccepted = $currentStatus === 'draft' ? $faker->boolean(30) : $faker->boolean(90);
            
            // Determinar si la aplicación está completada
            $applicationCompleted = in_array($currentStatus, ['approved', 'rejected']) ? true : $faker->boolean(60);
            
            // Generar paso actual basado en el estado
            $currentStep = match($currentStatus) {
                'draft' => $faker->numberBetween(1, 5),
                'pending' => $faker->numberBetween(6, 9),
                'approved', 'rejected' => 10,
                default => $faker->numberBetween(1, 10)
            };
            
            // Calcular porcentaje de completado
            $completionPercentage = ($currentStep / 10) * 100;
            if ($applicationCompleted) {
                $completionPercentage = 100;
            }
            
            // Crear UserDriverDetail
            $userDriverDetail = UserDriverDetail::create([
                'user_id' => $user->id,
                'carrier_id' => $carrier->id,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'phone' => $phone,
                'date_of_birth' => $dateOfBirth,
                'status' => $userDriverStatus,
                'terms_accepted' => $termsAccepted,
                'confirmation_token' => $faker->optional(0.3)->uuid,
                'application_completed' => $applicationCompleted,
                'current_step' => $currentStep,
                'completion_percentage' => $completionPercentage,
                'use_custom_dates' => false,
                'created_at' => $user->created_at,
                'updated_at' => now(),
            ]);
            
            // Crear DriverApplication
            $completedAt = null;
            if (in_array($currentStatus, ['approved', 'rejected'])) {
                $completedAt = $faker->dateTimeBetween($user->created_at, 'now');
            }
            
            $driverApplication = DriverApplication::create([
                'user_id' => $user->id,
                'status' => $currentStatus,
                'completed_at' => $completedAt,
                'rejection_reason' => $currentStatus === 'rejected' ? $faker->sentence() : null,
                'created_at' => $user->created_at,
                'updated_at' => now(),
            ]);
            
            // Crear VehicleDriverAssignment para el Vehicle Assignment Type
            $assignmentTypes = ['company_driver', 'owner_operator', 'third_party'];
            $assignmentType = $faker->randomElement($assignmentTypes);
            
            $vehicleDriverAssignment = VehicleDriverAssignment::create([
                'user_driver_detail_id' => $userDriverDetail->id,
                'status' => $faker->randomElement(['pending', 'active', 'inactive']),
                'start_date' => $faker->dateTimeBetween($user->created_at, 'now'),
                'end_date' => $faker->optional(0.3)->dateTimeBetween('now', '+1 year'),
                'notes' => $faker->optional(0.5)->sentence(),
                'created_at' => $user->created_at,
                'updated_at' => now(),
            ]);

            // Crear DriverApplicationDetail
            $position = $faker->randomElement($positions);
            DriverApplicationDetail::create([
                'driver_application_id' => $driverApplication->id,
                'applying_position' => $position,
                'applying_location' => $faker->city . ', ' . $faker->stateAbbr,
                'eligible_to_work' => $faker->boolean(95),
                'can_speak_english' => $faker->boolean(90),
                'has_twic_card' => $faker->boolean(30),
                'twic_expiration_date' => $faker->optional(0.3)->dateTimeBetween('now', '+5 years'),
                'how_did_hear' => $faker->randomElement(['website', 'referral', 'job_board', 'social_media', 'other']),
                'expected_pay' => $faker->randomFloat(2, 50000, 80000),
                'has_completed_employment_history' => $applicationCompleted,
                'vehicle_driver_assignment_id' => $vehicleDriverAssignment->id,
                'created_at' => $user->created_at,
                'updated_at' => now(),
            ]);
            
            // Crear DriverAddress (dirección principal)
            DriverAddress::create([
                'driver_application_id' => $driverApplication->id,
                'primary' => true,
                'address_line1' => $faker->streetAddress,
                'address_line2' => $faker->optional(0.3)->secondaryAddress,
                'city' => $faker->city,
                'state' => $faker->stateAbbr,
                'zip_code' => $faker->postcode,
                'lived_three_years' => $faker->boolean(70),
                'from_date' => $faker->dateTimeBetween('-10 years', '-1 year'),
                'to_date' => null, // Dirección actual
                'created_at' => $user->created_at,
                'updated_at' => now(),
            ]);
            
            // Crear DriverLicense (al menos una licencia)
            $licenseCount = $faker->numberBetween(1, 2);
            for ($j = 0; $j < $licenseCount; $j++) {
                $issuedDate = $faker->dateTimeBetween('-10 years', '-1 year');
                $expirationDate = $faker->dateTimeBetween('now', '+8 years');
                
                $licenseNumber = strtoupper($faker->bothify('??########'));
                DriverLicense::create([
                    'user_driver_detail_id' => $userDriverDetail->id,
                    'current_license_number' => $licenseNumber,
                    'license_number' => $licenseNumber,
                    'state_of_issue' => $faker->randomElement($states),
                    'license_class' => $faker->randomElement(['A', 'B', 'C', 'CDL-A', 'CDL-B']),
                    'expiration_date' => $expirationDate,
                    'is_cdl' => $faker->boolean(70),
                    'restrictions' => $faker->optional(0.2)->sentence(3),
                    'status' => 'active',
                    'is_primary' => $j === 0, // Primera licencia es primaria
                    'created_at' => $issuedDate,
                    'updated_at' => now(),
                ]);
            }
            
            // Crear DriverMedicalQualification
            $medicalExpirationDate = $faker->dateTimeBetween('now', '+2 years');
            DriverMedicalQualification::create([
                'user_driver_detail_id' => $userDriverDetail->id,
                'hire_date' => $faker->optional(0.7)->dateTimeBetween('-2 years', 'now'),
                'location' => $faker->city . ', ' . $faker->stateAbbr,
                'is_suspended' => $faker->boolean(5),
                'is_terminated' => $faker->boolean(5),
                'medical_examiner_name' => 'Dr. ' . $faker->lastName,
                'medical_examiner_registry_number' => $faker->numerify('########'),
                'medical_card_expiration_date' => $medicalExpirationDate,
                'created_at' => $user->created_at,
                'updated_at' => now(),
            ]);
            
            // Crear DriverExperience (1-3 experiencias)
            $experienceCount = $faker->numberBetween(1, 3);
            for ($k = 0; $k < $experienceCount; $k++) {
                DriverExperience::create([
                    'user_driver_detail_id' => $userDriverDetail->id,
                    'equipment_type' => $faker->randomElement($equipmentTypes),
                    'years_experience' => $faker->numberBetween(1, 15),
                    'miles_driven' => $faker->numberBetween(50000, 999999), // Reducir el rango para evitar overflow
                    'requires_cdl' => $faker->boolean(80),
                    'created_at' => $user->created_at,
                    'updated_at' => now(),
                ]);
            }
            
            $driversCreated++;
            
            // Mostrar progreso cada 5 conductores
            if ($driversCreated % 5 === 0) {
                $this->command->info("Creados {$driversCreated} conductores completos...");
            }
        }
        
        $this->command->info('UserDriverSeeder completado exitosamente!');
        $this->command->info("Se crearon {$totalDrivers} conductores con aplicaciones completas.");
        
        // Mostrar estadísticas detalladas
        $draftApps = DriverApplication::where('status', 'draft')->count();
        $pendingApps = DriverApplication::where('status', 'pending')->count();
        $approvedApps = DriverApplication::where('status', 'approved')->count();
        $rejectedApps = DriverApplication::where('status', 'rejected')->count();
        $totalLicenses = DriverLicense::count();
        $totalMedical = DriverMedicalQualification::count();
        $totalExperiences = DriverExperience::count();
        $totalAddresses = DriverAddress::count();
        
        $this->command->info('Estadísticas detalladas:');
        $this->command->info("- Aplicaciones en borrador: {$draftApps}");
        $this->command->info("- Aplicaciones pendientes: {$pendingApps}");
        $this->command->info("- Aplicaciones aprobadas: {$approvedApps}");
        $this->command->info("- Aplicaciones rechazadas: {$rejectedApps}");
        $this->command->info("- Total licencias creadas: {$totalLicenses}");
        $this->command->info("- Total calificaciones médicas: {$totalMedical}");
        $this->command->info("- Total experiencias: {$totalExperiences}");
        $this->command->info("- Total direcciones: {$totalAddresses}");
    }
}