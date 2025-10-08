<?php

namespace Database\Factories;

use App\Models\CompanyDriverDetail;
use App\Models\VehicleDriverAssignment;
use App\Models\User;
use App\Models\Admin\Driver\DriverApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin\Vehicle\CompanyDriverDetail>
 */
class CompanyDriverDetailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CompanyDriverDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assignment_id' => VehicleDriverAssignment::factory(),
            'driver_application_id' => DriverApplication::factory(),
            'employee_id' => $this->faker->numerify('EMP####'),
            'department' => $this->faker->randomElement(['Operations', 'Logistics', 'Maintenance', 'Administration']),
            'supervisor_name' => $this->faker->name(),
            'supervisor_phone' => $this->faker->phoneNumber(),
            'salary_type' => $this->faker->randomElement(['hourly', 'salary', 'commission', 'per_mile']),
            'base_rate' => $this->faker->randomFloat(2, 15, 50),
            'overtime_rate' => $this->faker->randomFloat(2, 20, 75),
            'benefits_eligible' => $this->faker->boolean(70),
        ];
    }

    /**
     * Create a company driver detail for a specific assignment.
     */
    public function forAssignment($assignment): static
    {
        return $this->state(fn (array $attributes) => [
            'assignment_id' => is_object($assignment) ? $assignment->id : $assignment,
        ]);
    }

    /**
     * Create a company driver detail with hourly salary type.
     */
    public function hourly(): static
    {
        return $this->state(fn (array $attributes) => [
            'salary_type' => 'hourly',
            'base_rate' => $this->faker->randomFloat(2, 15, 35),
            'overtime_rate' => $this->faker->randomFloat(2, 22, 52),
        ]);
    }

    /**
     * Create a company driver detail with salary type.
     */
    public function salary(): static
    {
        return $this->state(fn (array $attributes) => [
            'salary_type' => 'salary',
            'base_rate' => $this->faker->randomFloat(2, 40000, 80000),
            'overtime_rate' => null,
        ]);
    }
}