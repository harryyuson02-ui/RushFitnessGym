<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
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
            'subscription_id' => null,
            'membership_plan_id' => MembershipPlan::factory(),
            'amount' => fake()->randomFloat(2, 500, 5000),
            'payment_method' => fake()->randomElement(['cash', 'gcash']),
            'reference' => fake()->optional()->numerify('##########'),
            'receipt_path' => null,
            'status' => 'paid',
            'paid_at' => now(),
        ];
    }
}
