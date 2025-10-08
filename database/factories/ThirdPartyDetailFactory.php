<?php

namespace Database\Factories;

use App\Models\ThirdPartyDetail;
use App\Models\VehicleDriverAssignment;
use App\Models\Admin\Driver\DriverApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin\Vehicle\ThirdPartyDetail>
 */
class ThirdPartyDetailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ThirdPartyDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assignment_id' => VehicleDriverAssignment::factory(),
            'third_party_name' => $this->faker->company(),
            'third_party_phone' => $this->faker->phoneNumber(),
            'third_party_email' => $this->faker->safeEmail(),
            'third_party_dba' => $this->faker->optional()->company(),
            'third_party_address' => $this->faker->optional()->address(),
            'third_party_contact' => $this->faker->optional()->name(),
            'third_party_fein' => $this->faker->optional()->numerify('##-#######'),
            'notes' => $this->faker->optional()->paragraph(),
        ];
    }

    /**
     * Indicate that the third party has complete information.
     */
    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'third_party_name' => $this->faker->company(),
            'third_party_phone' => $this->faker->phoneNumber(),
            'third_party_email' => $this->faker->safeEmail(),
            'third_party_dba' => $this->faker->company(),
            'third_party_address' => $this->faker->address(),
            'third_party_contact' => $this->faker->name(),
            'third_party_fein' => $this->faker->numerify('##-#######'),
            'notes' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Indicate that the third party has minimal information.
     */
    public function minimal(): static
    {
        return $this->state(fn (array $attributes) => [
            'third_party_email' => null,
            'third_party_dba' => null,
            'third_party_address' => null,
            'third_party_contact' => null,
            'third_party_fein' => null,
            'notes' => null,
        ]);
    }

    /**
     * Create a third party detail for a specific assignment.
     */
    public function forAssignment($assignment): static
    {
        return $this->state(fn (array $attributes) => [
            'assignment_id' => is_object($assignment) ? $assignment->id : $assignment,
        ]);
    }

    /**
     * Create a third party with a specific company name.
     */
    public function withCompany(string $companyName): static
    {
        return $this->state(fn (array $attributes) => [
            'third_party_name' => $companyName,
        ]);
    }
}