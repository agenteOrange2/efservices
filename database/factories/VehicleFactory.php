<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Carrier;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Vehicle::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'carrier_id' => Carrier::factory(), // Default carrier ID for testing
            'make' => $this->faker->randomElement(['Ford', 'Chevrolet', 'Toyota', 'Honda', 'Nissan', 'Freightliner', 'Peterbilt', 'Kenworth']),
            'model' => $this->faker->randomElement(['Transit', 'Express', 'Sprinter', 'ProMaster', 'Cascadia', '579', 'T680']),
            'type' => $this->faker->randomElement(['truck', 'trailer', 'van', 'car']),
            'company_unit_number' => $this->faker->optional()->numerify('UNIT-####'),
            'year' => $this->faker->numberBetween(2015, 2024),
            'vin' => strtoupper($this->faker->bothify('?#?#?#?#?#?#?#?#?')),
            'gvwr' => $this->faker->optional()->numberBetween(10000, 80000),
            'registration_state' => $this->faker->stateAbbr(),
            'registration_number' => strtoupper($this->faker->bothify('???-####')),
            'registration_expiration_date' => $this->faker->dateTimeBetween('now', '+2 years')->format('Y-m-d'),
            'permanent_tag' => $this->faker->boolean(20),
            'tire_size' => $this->faker->optional()->randomElement(['275/70R22.5', '295/75R22.5', '11R22.5']),
            'fuel_type' => $this->faker->randomElement(['gasoline', 'diesel', 'electric', 'hybrid']),
            'irp_apportioned_plate' => $this->faker->boolean(30),
            'ownership_type' => $this->faker->randomElement(['owned', 'leased', 'third_party', 'unassigned']),
            'driver_type' => $this->faker->optional()->randomElement(['owner_operator', 'third_party', 'company']),
            'location' => $this->faker->optional()->city(),
            'user_driver_detail_id' => null,
            'annual_inspection_expiration_date' => $this->faker->boolean(70) ? $this->faker->dateTimeBetween('now', '+1 year')->format('Y-m-d') : null,
            'out_of_service' => $this->faker->boolean(10),
            'out_of_service_date' => null,
            'suspended' => $this->faker->boolean(5),
            'status' => $this->faker->randomElement(['pending', 'active', 'maintenance', 'inactive']),
            'suspended_date' => null,
            'notes' => $this->faker->optional()->sentence()
        ];
    }

    /**
     * Indicate that the vehicle is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the vehicle is in maintenance.
     */
    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'maintenance',
        ]);
    }

    /**
     * Indicate that the vehicle is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}