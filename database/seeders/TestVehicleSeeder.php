<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Carrier;
use App\Models\Admin\Driver\DriverApplicationDetail;

class TestVehicleSeeder extends Seeder
{
    public function run()
    {
        // Crear un carrier si no existe
        $carrier = Carrier::first();
        if (!$carrier) {
            $carrier = Carrier::create([
                'name' => 'Test Carrier',
                'mc_number' => 'MC123456',
                'dot_number' => 'DOT123456',
                'address' => '123 Test St',
                'city' => 'Dallas',
                'state' => 'TX',
                'zip' => '75001',
                'phone' => '555-0123',
                'email' => 'test@carrier.com'
            ]);
        }

        // Crear vehículo de prueba
        $vehicle = Vehicle::create([
            'carrier_id' => $carrier->id,
            'make' => 'Ford',
            'model' => 'F-150',
            'type' => 'Truck',
            'year' => 2020,
            'company_unit_number' => 'TEST001',
            'vin' => '1FTFW1ET5LFC12345',
            'gvwr' => 8500,
            'tire_size' => '265/70R17',
            'fuel_type' => 'Diesel',
            'irp_apportioned_plate' => true,
            'ownership_type' => 'owned',
            'location' => 'Dallas, TX',
            'annual_inspection_expiration_date' => '2024-12-31'
        ]);

        // Crear detalles del owner operator
        DriverApplicationDetail::create([
            'vehicle_id' => $vehicle->id,
            'ownership_type' => 'owned',
            'owner_name' => 'John Doe',
            'owner_phone' => '555-0123',
            'owner_email' => 'john@example.com'
        ]);

        echo "Vehicle created with ID: {$vehicle->id}\n";
    }
}