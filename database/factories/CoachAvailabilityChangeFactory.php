<?php

namespace Database\Factories;

use App\Models\Coach;
use App\Models\CoachAvailabilityChange;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CoachAvailabilityChange>
 */
class CoachAvailabilityChangeFactory extends Factory
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
            'submitted_by' => User::factory()->coach(),
            'slots' => [
                ['day_of_week' => 1, 'starts_at' => '08:00', 'ends_at' => '17:00', 'is_available' => true],
            ],
            'is_available' => true,
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];
    }
}
