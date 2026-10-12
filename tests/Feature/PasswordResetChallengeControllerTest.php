<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\PasswordResetChallengeController;
use App\Models\Member;
use App\Models\User;
use App\Notifications\PasswordResetCode;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PasswordResetChallengeControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_verification_page_uses_six_numeric_code_boxes(): void
    {
        Notification::fake([PasswordResetCode::class]);

        $this->post(route('password.send-code'), ['email' => 'missing@example.test']);

        $response = $this->get(route('password.code'));

        $response->assertOk()
            ->assertSee('data-verification-code', false)
            ->assertSee('data-code-digit', false)
            ->assertSee('name="code_digits[]"', false)
            ->assertSee('inputmode="numeric"', false);

        $this->assertSame(6, substr_count($response->getContent(), 'data-code-digit'));
    }

    public function test_new_password_page_has_visibility_controls_for_both_fields(): void
    {
        $user = User::factory()->member()->create();

        $response = $this->withSession([
            PasswordResetChallengeController::VERIFIED_USER_KEY => $user->id,
            PasswordResetChallengeController::VERIFIED_AT_KEY => now()->timestamp,
        ])->get(route('password.reset'));

        $response->assertOk()
            ->assertSee('id="reset-password"', false)
            ->assertSee('id="reset-password-confirmation"', false)
            ->assertSee('aria-controls="reset-password"', false)
            ->assertSee('aria-controls="reset-password-confirmation"', false);

        $this->assertSame(2, substr_count($response->getContent(), 'data-password-toggle'));
    }

    public function test_login_page_links_to_member_password_recovery(): void
    {
        $this->get(route('login'))
            ->assertSee('Forgot password?')
            ->assertSee(route('password.request'))
            ->assertDontSee('Email me a verification code');

        $this->post('/login/code', ['email' => 'member@example.test'])->assertNotFound();
    }

    #[DataProvider('portalRoles')]
    public function test_each_portal_role_receives_a_reset_code_at_the_account_email_address(string $role): void
    {
        $user = User::factory()->create(['email' => 'portal@example.test', 'role' => $role]);

        if ($role === 'user') {
            Member::factory()->for($user)->create();
        }

        Notification::fake([PasswordResetCode::class]);

        $response = $this->post(route('password.send-code'), ['email' => 'portal@example.test']);

        $response->assertRedirect(route('password.code'))->assertSessionHas('status');
        $this->assertSame('portal@example.test', $user->routeNotificationFor('mail'));
        Notification::assertSentTo(
            $user,
            PasswordResetCode::class,
            function (PasswordResetCode $notification, array $channels) use ($user): bool {
                $message = $notification->toMail($user);

                return $channels === ['mail']
                    && preg_match('/^\d{6}$/', $notification->code) === 1
                    && in_array($notification->code, $message->introLines, true);
            },
        );
    }

    /** @return array<string, array{string}> */
    public static function portalRoles(): array
    {
        return [
            'administrator' => ['admin'],
            'coach' => ['coach'],
            'member' => ['user'],
        ];
    }

    public function test_unknown_email_gets_the_same_recovery_response_without_sending_a_code(): void
    {
        Notification::fake([PasswordResetCode::class]);

        $response = $this->post(route('password.send-code'), ['email' => 'missing@example.test']);

        $response->assertRedirect(route('password.code'))->assertSessionHas('status');
        Notification::assertNothingSent();
    }
}
