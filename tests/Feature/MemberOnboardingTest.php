<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class MemberOnboardingTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_members_interface_is_a_read_only_list(): void
    {
        $admin = User::factory()->create();
        $member = Member::factory()->create(['first_name' => 'Ana', 'last_name' => 'Reyes']);

        $this->actingAs($admin)->get(route('admin.members.index'))
            ->assertOk()
            ->assertSee($member->full_name)
            ->assertDontSee('Add member');
        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertDontSee('Onboard member');
    }

    public function test_admin_member_forms_and_write_endpoints_are_unavailable(): void
    {
        $admin = User::factory()->create();
        $member = Member::factory()->create();

        $this->actingAs($admin)->get('/admin/members/create')->assertNotFound();
        $this->actingAs($admin)->get("/admin/members/{$member->id}")->assertNotFound();
        $this->actingAs($admin)->post('/admin/members', [])->assertStatus(405);
        $this->actingAs($admin)->post("/admin/members/{$member->id}/renewal", [])->assertNotFound();
    }
}
