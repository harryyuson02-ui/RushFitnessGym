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
    public function configure(): static
    {
        return $this->afterCreating(function (Transaction $transaction): void {
            if ($transaction->payment_method === 'gcash' && ! $transaction->gcashPayment()->exists()) {
                $transaction->gcashPayment()->create([
                    'reference' => $transaction->reference ?? fake()->numerify('#############'),
                    'receipt_path' => $transaction->receipt_path ?? 'receipts/factory-receipt.pdf',
                    'verification_status' => match ($transaction->status) {
                        'paid' => 'verified',
                        'rejected' => 'rejected',
                        default => 'pending',
                    },
                    'verified_by' => $transaction->verified_by,
                    'verified_at' => $transaction->verified_at,
                    'verification_notes' => $transaction->verification_notes,
                ]);
            }

            if ($transaction->payment_method === 'cash' && ! $transaction->cashPayment()->exists()) {
                $transaction->cashPayment()->create([
                    'received_at' => $transaction->status === 'paid' ? ($transaction->paid_at ?? now()) : null,
                ]);
            }
        });
    }

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
            'reference' => fake()->optional()->numerify('#############'),
            'receipt_path' => null,
            'status' => 'paid',
            'paid_at' => now(),
        ];
    }
}
