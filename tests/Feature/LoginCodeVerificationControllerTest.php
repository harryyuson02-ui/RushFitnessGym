<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\LoginCodeChallengeController;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LoginCodeVerificationControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[DataProvider('portalRoles')]
    public function test_valid_code_signs_in_each_portal_role(string $role, string $routeName): void
    {
        $user = User::factory()->create(['role' => $role]);
        $challengeId = $this->storeChallenge($user, '123456');

        $response = $this->withSession([
            LoginCodeChallengeController::SESSION_KEY => $challengeId,
        ])->post(route('login.code.verify'), ['code' => '123456']);

        $response->assertRedirect(route($routeName))
            ->assertSessionMissing(LoginCodeChallengeController::SESSION_KEY);
        $this->assertAuthenticatedAs($user);
        $this->assertFalse(Cache::has(LoginCodeChallengeController::CACHE_PREFIX.$challengeId));
    }

    /** @return array<string, array{string, string}> */
    public static function portalRoles(): array
    {
        return [
            'administrator' => ['admin', 'admin.dashboard'],
            'coach' => ['coach', 'coach.dashboard'],
            'member' => ['user', 'member.dashboard'],
        ];
    }

    public function test_invalid_code_increments_the_attempt_count_without_signing_in(): void
    {
        $user = User::factory()->create();
        $challengeId = $this->storeChallenge($user, '123456');

        $response = $this->withSession([
            LoginCodeChallengeController::SESSION_KEY => $challengeId,
        ])->from(route('login.code'))->post(route('login.code.verify'), ['code' => '654321']);

        $response->assertRedirect(route('login.code'))->assertSessionHasErrors([
            'code' => 'The verification code is invalid or has expired.',
        ]);
        $this->assertGuest();
        $challenge = Cache::get(LoginCodeChallengeController::CACHE_PREFIX.$challengeId);
        $this->assertSame(1, $challenge['attempts']);
    }

    public function test_fifth_invalid_code_invalidates_the_challenge(): void
    {
        $user = User::factory()->create();
        $challengeId = $this->storeChallenge($user, '123456', attempts: 4);

        $response = $this->withSession([
            LoginCodeChallengeController::SESSION_KEY => $challengeId,
        ])->post(route('login.code.verify'), ['code' => '654321']);

        $response->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'The verification code is invalid or has expired. Request a new code.'])
            ->assertSessionMissing(LoginCodeChallengeController::SESSION_KEY);
        $this->assertGuest();
        $this->assertFalse(Cache::has(LoginCodeChallengeController::CACHE_PREFIX.$challengeId));
    }

    public function test_verification_page_redirects_to_login_when_the_challenge_expired(): void
    {
        $user = User::factory()->create();
        $challengeId = $this->storeChallenge($user, '123456', expiresAt: now()->subMinute()->timestamp);

        $response = $this->withSession([
            LoginCodeChallengeController::SESSION_KEY => $challengeId,
        ])->get(route('login.code'));

        $response->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'Request a new verification code to continue.'])
            ->assertSessionMissing(LoginCodeChallengeController::SESSION_KEY);
        $this->assertGuest();
    }

    private function storeChallenge(
        User $user,
        string $code,
        int $attempts = 0,
        ?int $expiresAt = null,
    ): string {
        $challengeId = 'login-challenge-'.$user->id;
        $expirationTimestamp = $expiresAt ?? now()->addMinutes(10)->timestamp;

        Cache::put(LoginCodeChallengeController::CACHE_PREFIX.$challengeId, [
            'user_id' => $user->id,
            'code_hash' => Hash::make($code),
            'attempts' => $attempts,
            'expires_at' => $expirationTimestamp,
            'remember' => false,
        ], now()->addMinutes(10));

        return $challengeId;
    }
}
