<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Member;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'subscription_id' => fn (array $attributes) => Subscription::factory()->create([
                'member_id' => $attributes['member_id'],
            ])->id,
            'scanned_by' => User::factory(),
            'attendance_date' => today(),
            'checked_in_at' => now(),
        ];
    }
}
