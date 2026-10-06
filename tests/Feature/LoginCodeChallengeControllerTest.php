<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\LoginCodeChallengeController;
use App\Models\User;
use App\Notifications\LoginVerificationCode;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LoginCodeChallengeControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_login_page_offers_email_verification_code_sign_in(): void
    {
        $this->get(route('login'))
            ->assertSee('Email me a verification code')
            ->assertSee(route('login.code.send'));
    }

    public function test_portal_account_receives_a_verification_code_at_its_email_address(): void
    {
        $user = User::factory()->create(['email' => 'member@gmail.com']);
        Notification::fake([LoginVerificationCode::class]);

        $response = $this->post(route('login.code.send'), [
            'email' => 'member@gmail.com',
            'remember' => '1',
        ]);

        $response->assertRedirect(route('login.code'))
            ->assertSessionHas('status')
            ->assertSessionHas(LoginCodeChallengeController::SESSION_KEY);
        $challengeId = session(LoginCodeChallengeController::SESSION_KEY);
        $challenge = Cache::get(LoginCodeChallengeController::CACHE_PREFIX.$challengeId);
        $this->assertTrue($challenge['remember']);
        Notification::assertSentTo(
            $user,
            LoginVerificationCode::class,
            function (LoginVerificationCode $notification, array $channels) use ($user): bool {
                $message = $notification->toMail($user);

                return $channels === ['mail']
                    && preg_match('/^\d{6}$/', $notification->code) === 1
                    && in_array($notification->code, $message->introLines, true);
            },
        );
    }

    public function test_unknown_email_gets_the_same_response_without_sending_a_code(): void
    {
        Notification::fake([LoginVerificationCode::class]);

        $response = $this->post(route('login.code.send'), ['email' => 'missing@gmail.com']);

        $response->assertRedirect(route('login.code'))
            ->assertSessionHas('status')
            ->assertSessionHas(LoginCodeChallengeController::SESSION_KEY);
        Notification::assertNothingSent();
    }

    public function test_invalid_email_is_rejected_before_a_code_is_sent(): void
    {
        Notification::fake([LoginVerificationCode::class]);

        $response = $this->from(route('login'))->post(route('login.code.send'), ['email' => 'not-an-email']);

        $response->assertRedirect(route('login'))->assertSessionHasErrors([
            'email' => 'The email field must be a valid email address.',
        ]);
        Notification::assertNothingSent();
    }
}
