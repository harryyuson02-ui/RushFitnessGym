<?php

namespace Tests\Feature\Admin;

use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class MembershipPlanControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_navigation_links_to_subscription_plan_management(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertSeeText('Plans')
            ->assertSee(route('admin.plans.index'), false);
    }

    public function test_admin_can_view_subscription_plan_management(): void
    {
        $admin = User::factory()->create();
        $plan = MembershipPlan::factory()->create([
            'name' => 'Monthly Access',
            'description' => 'Thirty days of gym access.',
            'duration_days' => 30,
            'price' => 1200,
            'is_active' => true,
        ]);

        $this->actingAs($admin)->get(route('admin.plans.index'))
            ->assertSeeText('Monthly Access')
            ->assertSee(route('admin.plans.edit', $plan), false);
    }

    public function test_admin_can_edit_a_subscription_plan(): void
    {
        $admin = User::factory()->create();
        $plan = MembershipPlan::factory()->create([
            'name' => 'Monthly Access',
            'description' => 'Thirty days of gym access.',
            'duration_days' => 30,
            'price' => 1200,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.plans.update', $plan), [
            'name' => 'Monthly Plus',
            'description' => 'Updated monthly gym access.',
            'duration_days' => 31,
            'price' => 1350,
            'is_active' => '0',
        ]);

        $response
            ->assertRedirect(route('admin.plans.index'))
            ->assertSessionHas('success', 'Membership plan updated.');
        $this->assertDatabaseHas('membership_plans', [
            'id' => $plan->id,
            'name' => 'Monthly Plus',
            'description' => 'Updated monthly gym access.',
            'duration_days' => 31,
            'price' => 1350,
            'is_active' => false,
        ]);
    }

    public function test_member_cannot_edit_a_subscription_plan(): void
    {
        $member = User::factory()->member()->create();
        $plan = MembershipPlan::factory()->create(['name' => 'Monthly Access']);

        $this->actingAs($member)->put(route('admin.plans.update', $plan), [
            'name' => 'Unauthorized change',
            'description' => 'This must not be saved.',
            'duration_days' => 1,
            'price' => 1,
            'is_active' => '0',
        ])->assertForbidden();

        $this->assertSame('Monthly Access', $plan->fresh()->name);
    }
}
