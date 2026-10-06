<?php

namespace Tests\Feature\Member;

use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class MembershipPlanControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_new_member_sees_active_plans_in_dashboard_onboarding_without_admin_controls(): void
    {
        $user = User::factory()->member()->create();
        MembershipPlan::factory()->create([
            'name' => 'Monthly Access',
            'price' => 1200,
            'duration_days' => 30,
        ]);
        MembershipPlan::factory()->create([
            'name' => 'Annual Access',
            'price' => 11000,
            'duration_days' => 365,
        ]);
        MembershipPlan::factory()->create([
            'name' => 'Retired Plan',
            'is_active' => false,
        ]);
        $response = $this->actingAs($user)->get(route('member.dashboard'));

        $response
            ->assertSeeText('Select a membership plan')
            ->assertSeeText('Monthly Access')
            ->assertSeeText('Annual Access')
            ->assertSeeText('₱1,200.00')
            ->assertDontSeeText('Retired Plan')
            ->assertDontSeeText('Add plan')
            ->assertDontSeeText('Edit plan')
            ->assertDontSee('/user/plans', false);

        $this->actingAs($user)->get('/user/plans')->assertNotFound();
    }

    public function test_plan_content_is_escaped(): void
    {
        $user = User::factory()->member()->create();
        MembershipPlan::factory()->create([
            'name' => '<script>alert("plans")</script>',
        ]);

        $this->actingAs($user)->get(route('member.dashboard'))
            ->assertSee('&lt;script&gt;alert(&quot;plans&quot;)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert("plans")</script>', false);
    }
}
