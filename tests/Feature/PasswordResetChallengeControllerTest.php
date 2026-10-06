<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use App\Notifications\PasswordResetCode;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetChallengeControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_login_page_links_to_member_password_recovery(): void
    {
        $this->get(route('login'))
            ->assertSee('Forgot password?')
            ->assertSee(route('password.request'));
    }

    public function test_member_receives_a_reset_code_at_the_account_email_address(): void
    {
        $user = User::factory()->member()->create(['email' => 'member@example.test']);
        Member::factory()->for($user)->create();
        Notification::fake([PasswordResetCode::class]);

        $response = $this->post(route('password.send-code'), ['email' => 'member@example.test']);

        $response->assertRedirect(route('password.code'))->assertSessionHas('status');
        $this->assertSame('member@example.test', $user->routeNotificationFor('mail'));
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

    public function test_unknown_email_gets_the_same_recovery_response_without_sending_a_code(): void
    {
        Notification::fake([PasswordResetCode::class]);

        $response = $this->post(route('password.send-code'), ['email' => 'missing@example.test']);

        $response->assertRedirect(route('password.code'))->assertSessionHas('status');
        Notification::assertNothingSent();
    }
}
