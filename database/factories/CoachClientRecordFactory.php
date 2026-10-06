<?php

namespace Database\Factories;

use App\Models\Coach;
use App\Models\CoachClientRecord;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CoachClientRecord>
 */
class CoachClientRecordFactory extends Factory
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
            'member_id' => Member::factory(),
            'title' => fake()->sentence(3),
            'notes' => fake()->paragraph(),
            'recorded_at' => today(),
        ];
    }
}
