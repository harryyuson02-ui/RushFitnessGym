<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\PasswordResetChallengeController;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetVerificationControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_authentication_code_authorizes_a_password_reset(): void
    {
        $user = User::factory()->member()->create();
        Member::factory()->for($user)->create();
        $challengeId = 'valid-challenge';
        Cache::put(PasswordResetChallengeController::CACHE_PREFIX.$challengeId, [
            'user_id' => $user->id,
            'code_hash' => Hash::make('123456'),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10)->timestamp,
        ], now()->addMinutes(10));

        $response = $this->withSession([
            PasswordResetChallengeController::SESSION_KEY => $challengeId,
        ])->post(route('password.verify-code'), ['code_digits' => ['1', '2', '3', '4', '5', '6']]);

        $response->assertRedirect(route('password.reset'))
            ->assertSessionHas(PasswordResetChallengeController::VERIFIED_USER_KEY, $user->id)
            ->assertSessionMissing(PasswordResetChallengeController::SESSION_KEY);
        $this->assertFalse(Cache::has(PasswordResetChallengeController::CACHE_PREFIX.$challengeId));
    }

    public function test_fifth_invalid_code_invalidates_the_challenge(): void
    {
        $user = User::factory()->member()->create();
        Member::factory()->for($user)->create();
        $challengeId = 'attempted-challenge';
        Cache::put(PasswordResetChallengeController::CACHE_PREFIX.$challengeId, [
            'user_id' => $user->id,
            'code_hash' => Hash::make('123456'),
            'attempts' => 4,
            'expires_at' => now()->addMinutes(10)->timestamp,
        ], now()->addMinutes(10));

        $response = $this->withSession([
            PasswordResetChallengeController::SESSION_KEY => $challengeId,
        ])->post(route('password.verify-code'), ['code' => '654321']);

        $response->assertRedirect(route('password.request'))
            ->assertSessionHasErrors(['email' => 'The authentication code is invalid or has expired. Request a new code.'])
            ->assertSessionMissing(PasswordResetChallengeController::SESSION_KEY);
        $this->assertFalse(Cache::has(PasswordResetChallengeController::CACHE_PREFIX.$challengeId));
    }
}
