<?php

namespace Database\Factories;

use App\Models\UserDriverDetail;
use App\Models\User;
use App\Models\Carrier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserDriverDetail>
 */
class UserDriverDetailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserDriverDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'carrier_id' => Carrier::factory(),
            'middle_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'phone' => $this->faker->phoneNumber(),
            'date_of_birth' => $this->faker->date(),
            'status' => $this->faker->numberBetween(0, 2),
            'terms_accepted' => $this->faker->boolean(),
            'confirmation_token' => $this->faker->uuid(),
            'application_completed' => $this->faker->boolean(),
            'current_step' => $this->faker->numberBetween(1, 10),
            'completion_percentage' => $this->faker->numberBetween(0, 100),
            'use_custom_dates' => false,
            'custom_created_at' => null,
            'custom_registration_date' => null,
            'custom_completion_date' => null,
        ];
    }

    /**
     * Indicate that the driver application is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'application_completed' => true,
            'current_step' => 10, // Assuming 10 is the final step
        ]);
    }

    /**
     * Indicate that the driver uses custom dates.
     */
    public function withCustomDates(): static
    {
        return $this->state(fn (array $attributes) => [
            'use_custom_dates' => true,
            'custom_created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'custom_registration_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'custom_completion_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ]);
    }
}