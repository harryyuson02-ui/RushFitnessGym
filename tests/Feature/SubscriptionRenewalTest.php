<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SubscriptionRenewalTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_cannot_renew_a_membership_from_the_read_only_directory(): void
    {
        $admin = User::factory()->create();
        $subscription = Subscription::factory()->create();

        $this->actingAs($admin)->post("/admin/members/{$subscription->member_id}/renewal", [
            'membership_plan_id' => $subscription->membership_plan_id,
            'starts_at' => '2026-10-01',
            'expires_at' => '2026-10-30',
            'payment_confirmed' => '1',
        ])->assertNotFound();

        $this->assertDatabaseCount('subscriptions', 1);
    }
}
