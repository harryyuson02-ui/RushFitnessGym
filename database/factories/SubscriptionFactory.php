<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
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
            'membership_plan_id' => MembershipPlan::factory(),
            'starts_at' => today(),
            'expires_at' => today()->addDays(30),
            'status' => 'active',
            'access_token' => (string) Str::uuid(),
            'paid_at' => now(),
            'qr_issued_at' => now(),
            'reminder_sent_at' => null,
        ];
    }
}
