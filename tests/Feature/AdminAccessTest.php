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

    public function test_non_admin_user_is_forbidden_from_admin_dashboard(): void
    {
        $user = User::factory()->nonAdmin()->create();

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_public_registration_is_not_available(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register')->assertNotFound();
    }

    public function test_admin_can_sign_in_with_valid_credentials(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.test']);

        $this->post(route('login.submit'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        User::factory()->create(['email' => 'admin@example.test']);

        $this->from(route('login'))->post(route('login.submit'), [
            'email' => 'admin@example.test',
            'password' => 'wrong-password',
        ])->assertRedirect(route('login'))->assertSessionHasErrors([
            'email' => 'These credentials do not match an authorized administrator.',
        ]);

        $this->assertGuest();
    }
}
