<?php

namespace Database\Factories;

use App\Models\VehicleDriverAssignment;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin\Vehicle\VehicleDriverAssignment>
 */
class VehicleDriverAssignmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = VehicleDriverAssignment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'user_id' => User::factory(),
            'assignment_type' => $this->faker->randomElement(['company_driver', 'owner_operator', 'third_party']),
            'status' => 'active',
            'assigned_by' => User::factory(),
            'assigned_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'effective_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'termination_date' => null,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the assignment is for a company driver.
     */
    public function companyDriver(): static
    {
        return $this->state(fn (array $attributes) => [
            'assignment_type' => 'company_driver',
        ]);
    }

    /**
     * Indicate that the assignment is for an owner operator.
     */
    public function ownerOperator(): static
    {
        return $this->state(fn (array $attributes) => [
            'assignment_type' => 'owner_operator',
        ]);
    }

    /**
     * Indicate that the assignment is for a third party.
     */
    public function thirdParty(): static
    {
        return $this->state(fn (array $attributes) => [
            'assignment_type' => 'third_party',
        ]);
    }

    /**
     * Indicate that the assignment is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'termination_date' => null,
        ]);
    }

    /**
     * Indicate that the assignment has ended.
     */
    public function ended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'terminated',
            'termination_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ]);
    }
}