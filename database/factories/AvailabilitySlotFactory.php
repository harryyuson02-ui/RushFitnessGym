<?php

namespace Database\Factories;

use App\Models\AvailabilitySlot;
use App\Models\Coach;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AvailabilitySlot>
 */
class AvailabilitySlotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'coach_id' => Coach::factory(),
            'day_of_week' => fake()->numberBetween(1, 6),
            'starts_at' => '08:00',
            'ends_at' => '17:00',
            'is_available' => true,
        ];
    }
}
