<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_registration_page_has_password_visibility_controls(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('id="register-password"', false)
            ->assertSee('aria-controls="register-password"', false)
            ->assertSee('id="register-password-confirmation"', false)
            ->assertSee('aria-controls="register-password-confirmation"', false)
            ->assertSee('data-password-toggle', false);
    }

    public function test_guest_can_create_a_member_account_and_continue_to_onboarding(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Ana Reyes',
            'email' => 'ana@example.test',
            'password' => 'fitness123',
            'password_confirmation' => 'fitness123',
            'role' => 'admin',
        ]);

        $user = User::where('email', 'ana@example.test')->firstOrFail();

        $response->assertRedirect(route('member.dashboard'))->assertSessionHas('success');
        $this->assertAuthenticatedAs($user);
        $this->assertSame('user', $user->role);
        $this->assertNull($user->member);
    }

    public function test_registration_requires_a_unique_email_and_secure_confirmed_password(): void
    {
        User::factory()->create(['email' => 'used@example.test']);

        $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Ana Reyes',
            'email' => 'used@example.test',
            'password' => 'weak',
            'password_confirmation' => 'different',
        ])->assertRedirect(route('register'))->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
    }
}
