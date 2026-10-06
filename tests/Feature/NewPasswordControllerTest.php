<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\PasswordResetChallengeController;
use App\Models\Member;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NewPasswordControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_verified_member_can_set_a_new_password(): void
    {
        $user = User::factory()->member()->create(['remember_token' => 'old-token']);
        Member::factory()->for($user)->create();
        Event::fake([PasswordReset::class]);

        $response = $this->withSession([
            PasswordResetChallengeController::VERIFIED_USER_KEY => $user->id,
            PasswordResetChallengeController::VERIFIED_AT_KEY => now()->timestamp,
        ])->post(route('password.update'), [
            'password' => 'newpassword1',
            'password_confirmation' => 'newpassword1',
        ]);

        $response->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Your password has been changed. You can now sign in.')
            ->assertSessionMissing(PasswordResetChallengeController::VERIFIED_USER_KEY);
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword1', $user->password));
        $this->assertNotSame('old-token', $user->remember_token);
        Event::assertDispatched(PasswordReset::class, fn (PasswordReset $event): bool => $event->user->is($user));
    }

    public function test_unverified_request_cannot_change_a_member_password(): void
    {
        $user = User::factory()->member()->create();
        Member::factory()->for($user)->create();

        $response = $this->post(route('password.update'), [
            'password' => 'newpassword1',
            'password_confirmation' => 'newpassword1',
        ]);

        $response->assertRedirect(route('password.request'))
            ->assertSessionHasErrors(['email' => 'Verify an authentication code before choosing a new password.']);
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }
}
