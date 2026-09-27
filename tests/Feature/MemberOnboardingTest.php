<?php

namespace Tests\Feature;

use App\Models\Coach;
use App\Models\MembershipPlan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class MemberOnboardingTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_onboard_member_and_issue_qr_access_key(): void
    {
        $admin = User::factory()->create();
        $coach = Coach::factory()->create();
        $plan = MembershipPlan::factory()->create(['name' => 'Monthly']);

        $response = $this->actingAs($admin)->post(route('admin.members.store'), [
            'first_name' => 'Ana', 'last_name' => 'Reyes', 'birth_date' => '1995-05-10',
            'gender' => 'Female', 'phone' => '09171234567', 'email' => 'ana@example.test',
            'address' => 'Tagum City', 'preferred_channel' => 'whatsapp', 'coach_id' => $coach->id,
            'membership_plan_id' => $plan->id, 'starts_at' => '2026-09-28',
            'expires_at' => '2026-10-27', 'payment_confirmed' => '1',
        ]);

        $member = $coach->members()->firstOrFail();
        $subscription = Subscription::whereBelongsTo($member)->firstOrFail();

        $response->assertRedirect(route('admin.members.show', $member));
        $this->assertSame('Ana Reyes', $member->full_name);
        $this->assertSame('active', $subscription->status);
        $this->assertSame($plan->id, $subscription->membership_plan_id);
        $this->get(route('access-key.image', $subscription->access_token))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml');
    }

    public function test_onboarding_rejects_missing_required_details(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->from(route('admin.members.create'))->post(route('admin.members.store'), [])
            ->assertRedirect(route('admin.members.create'))
            ->assertSessionHasErrors([
                'first_name' => 'The first name field is required.',
                'last_name' => 'The last name field is required.',
                'phone' => 'The phone field is required.',
                'coach_id' => 'The coach id field is required.',
                'membership_plan_id' => 'The membership plan id field is required.',
                'payment_confirmed' => 'The payment confirmed field must be accepted.',
            ]);

        $this->assertDatabaseCount('members', 0);
    }
}
