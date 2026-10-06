<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_from_admin_dashboard_to_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_guest_can_open_the_portal_sign_in_page(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Portal sign in')
            ->assertSee('Create account')
            ->assertSee(route('login.submit'));
    }

    public function test_authenticated_user_can_open_the_portal_sign_in_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('login'))
            ->assertOk()
            ->assertSee('Portal sign in');
    }

    public function test_non_admin_user_is_forbidden_from_admin_dashboard(): void
    {
        $user = User::factory()->nonAdmin()->create();

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_public_member_registration_is_available(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Create your account')
            ->assertSee(route('register.store'));
    }

    public function test_admin_can_sign_in_with_valid_credentials(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.test']);

        $this->post(route('login.submit'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_authenticated_user_can_sign_in_to_another_portal_account(): void
    {
        $admin = User::factory()->create();
        $coach = User::factory()->coach()->create(['email' => 'coach@example.test']);

        $this->actingAs($admin)
            ->post(route('login.submit'), ['email' => $coach->email, 'password' => 'password'])
            ->assertRedirect(route('coach.dashboard'));

        $this->assertAuthenticatedAs($coach);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        User::factory()->create(['email' => 'admin@example.test']);

        $this->from(route('login'))->post(route('login.submit'), [
            'email' => 'admin@example.test',
            'password' => 'wrong-password',
        ])->assertRedirect(route('login'))->assertSessionHasErrors([
            'email' => 'These credentials do not match our records.',
        ]);

        $this->assertGuest();
    }
}
