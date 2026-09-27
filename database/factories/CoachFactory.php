<?php

namespace Database\Factories;

use App\Models\Coach;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coach>
 */
class CoachFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('09#########'),
            'specialty' => fake()->randomElement(['Strength', 'Conditioning', 'Boxing', 'Mobility']),
            'schedule' => 'Monday to Saturday, 8:00 AM - 5:00 PM',
            'is_available' => true,
        ];
    }
}
