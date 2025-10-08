<?php

namespace Database\Factories;

use App\Models\OwnerOperatorDetail;
use App\Models\VehicleDriverAssignment;
use App\Models\Admin\Driver\DriverApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin\Vehicle\OwnerOperatorDetail>
 */
class OwnerOperatorDetailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OwnerOperatorDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'driver_application_id' => DriverApplication::factory(),
            'assignment_id' => VehicleDriverAssignment::factory(),
            'owner_name' => $this->faker->name(),
            'owner_phone' => $this->faker->phoneNumber(),
            'owner_email' => $this->faker->safeEmail(),
            'contract_agreed' => $this->faker->boolean(80),
            'notes' => $this->faker->optional()->paragraph(),
        ];
    }

    /**
     * Indicate that the owner operator has complete information.
     */
    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'driver_email' => $this->faker->safeEmail(),
            'driver_license' => $this->faker->regexify('[A-Z]{2}[0-9]{8}'),
            'business_name' => $this->faker->company(),
            'tax_id' => $this->faker->numerify('##-#######'),
            'insurance_policy' => $this->faker->regexify('[A-Z]{3}[0-9]{7}'),
            'insurance_expiration' => $this->faker->dateTimeBetween('now', '+1 year'),
            'contract_rate' => $this->faker->randomFloat(2, 1.50, 3.00),
            'notes' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Indicate that the owner operator has minimal information.
     */
    public function minimal(): static
    {
        return $this->state(fn (array $attributes) => [
            'driver_email' => null,
            'driver_license' => null,
            'business_name' => null,
            'tax_id' => null,
            'insurance_policy' => null,
            'insurance_expiration' => null,
            'contract_rate' => null,
            'notes' => null,
        ]);
    }

    /**
     * Indicate that the owner operator has expired insurance.
     */
    public function expiredInsurance(): static
    {
        return $this->state(fn (array $attributes) => [
            'insurance_expiration' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }

    /**
     * Create an owner operator detail for a specific assignment.
     */
    public function forAssignment($assignment): static
    {
        return $this->state(fn (array $attributes) => [
            'assignment_id' => is_object($assignment) ? $assignment->id : $assignment,
        ]);
    }
}