<?php

namespace Tests\Feature;

use App\Models\MembershipPlan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SubscriptionRenewalTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_payment_confirmation_renews_subscription_and_rotates_qr_token(): void
    {
        $admin = User::factory()->create();
        $oldSubscription = Subscription::factory()->create(['access_token' => '85b90df0-ff3e-4426-a547-47944c95c185']);
        $newPlan = MembershipPlan::factory()->create(['name' => 'Annual']);

        $response = $this->actingAs($admin)->post(route('admin.members.renewal.store', $oldSubscription->member), [
            'membership_plan_id' => $newPlan->id,
            'starts_at' => '2026-10-01',
            'expires_at' => '2027-09-30',
            'payment_confirmed' => '1',
        ]);

        $newSubscription = $oldSubscription->member->subscriptions()->latest('id')->firstOrFail();

        $response->assertRedirect(route('admin.members.show', $oldSubscription->member));
        $this->assertSame('renewed', $oldSubscription->fresh()->status);
        $this->assertSame('active', $newSubscription->status);
        $this->assertNotSame($oldSubscription->access_token, $newSubscription->access_token);
        $this->assertSame($newPlan->id, $newSubscription->membership_plan_id);
    }

    public function test_renewal_requires_payment_confirmation(): void
    {
        $admin = User::factory()->create();
        $subscription = Subscription::factory()->create();

        $this->actingAs($admin)->from(route('admin.members.show', $subscription->member))
            ->post(route('admin.members.renewal.store', $subscription->member), [
                'membership_plan_id' => $subscription->membership_plan_id,
                'starts_at' => '2026-10-01',
                'expires_at' => '2026-10-30',
            ])->assertRedirect(route('admin.members.show', $subscription->member))
            ->assertSessionHasErrors(['payment_confirmed' => 'The payment confirmed field must be accepted.']);

        $this->assertDatabaseCount('subscriptions', 1);
    }
}
