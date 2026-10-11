<?php

namespace Database\Factories;

use App\Models\Coach;
use App\Models\CoachAssignmentRequest;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CoachAssignmentRequest>
 */
class CoachAssignmentRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'member_id' => Member::factory()->state(['coach_id' => null]),
            'coach_id' => Coach::factory(),
            'status' => 'pending',
            'reviewed_at' => null,
        ];
    }
}
