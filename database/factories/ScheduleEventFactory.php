<?php

namespace Database\Factories;

use App\Models\Coach;
use App\Models\Member;
use App\Models\ScheduleEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScheduleEvent>
 */
class ScheduleEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'type' => 'session',
            'starts_at' => now()->addDay()->setTime(9, 0),
            'ends_at' => now()->addDay()->setTime(10, 0),
            'coach_id' => Coach::factory(),
            'member_id' => Member::factory(),
            'created_by' => User::factory(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
