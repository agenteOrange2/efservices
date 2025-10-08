<?php

namespace Database\Factories;

use App\Models\Carrier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Carrier>
 */
class CarrierFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Carrier::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'slug' => $this->faker->unique()->slug(),
            'address' => $this->faker->streetAddress(),
            'state' => $this->faker->stateAbbr(),
            'zipcode' => $this->faker->postcode(),
            'country' => 'US',
            'ein_number' => $this->faker->numerify('##-#######'),
            'dot_number' => $this->faker->unique()->numerify('######'),
            'mc_number' => $this->faker->unique()->numerify('MC-######'),
            'business_type' => $this->faker->randomElement(['LLC', 'Corporation', 'Partnership', 'Sole Proprietorship']),
            'status' => Carrier::STATUS_ACTIVE,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the carrier is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Carrier::STATUS_ACTIVE,
        ]);
    }

    /**
     * Indicate that the carrier is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Carrier::STATUS_INACTIVE,
        ]);
    }
}