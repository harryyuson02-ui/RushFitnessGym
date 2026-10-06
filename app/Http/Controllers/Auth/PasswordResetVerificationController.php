<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyPasswordResetCodeRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PasswordResetVerificationController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if ($this->challenge() === null) {
            session()->forget(PasswordResetChallengeController::SESSION_KEY);

            return redirect()->route('password.request')->withErrors([
                'email' => 'Request a new authentication code to continue.',
            ]);
        }

        return view('auth.verify-password-reset-code');
    }

    public function store(VerifyPasswordResetCodeRequest $request): RedirectResponse
    {
        $challenge = $this->challenge();
        $challengeId = $request->session()->get(PasswordResetChallengeController::SESSION_KEY);

        if ($challenge === null || ! is_string($challengeId)) {
            return $this->expiredResponse($request);
        }

        $isValid = Hash::check($request->validated('code'), $challenge['code_hash']);
        $user = $isValid && is_int($challenge['user_id'])
            ? User::query()->where('role', 'user')->whereHas('member')->find($challenge['user_id'])
            : null;

        if ($user === null) {
            $challenge['attempts']++;

            if ($challenge['attempts'] >= PasswordResetChallengeController::MAX_ATTEMPTS) {
                return $this->expiredResponse($request);
            }

            Cache::put(
                PasswordResetChallengeController::CACHE_PREFIX.$challengeId,
                $challenge,
                now()->addSeconds(max(1, $challenge['expires_at'] - now()->timestamp)),
            );

            return back()->withErrors(['code' => 'The authentication code is invalid or has expired.']);
        }

        Cache::forget(PasswordResetChallengeController::CACHE_PREFIX.$challengeId);
        $request->session()->regenerate();
        $request->session()->forget(PasswordResetChallengeController::SESSION_KEY);
        $request->session()->put([
            PasswordResetChallengeController::VERIFIED_USER_KEY => $user->id,
            PasswordResetChallengeController::VERIFIED_AT_KEY => now()->timestamp,
        ]);

        return redirect()->route('password.reset');
    }

    /**
     * @return array{user_id: int|null, code_hash: string, attempts: int, expires_at: int}|null
     */
    private function challenge(): ?array
    {
        $challengeId = session(PasswordResetChallengeController::SESSION_KEY);

        if (! is_string($challengeId)) {
            return null;
        }

        $challenge = Cache::get(PasswordResetChallengeController::CACHE_PREFIX.$challengeId);

        if (! is_array($challenge) || $challenge['expires_at'] < now()->timestamp) {
            return null;
        }

        return $challenge;
    }

    private function expiredResponse(VerifyPasswordResetCodeRequest $request): RedirectResponse
    {
        $challengeId = $request->session()->pull(PasswordResetChallengeController::SESSION_KEY);

        if (is_string($challengeId)) {
            Cache::forget(PasswordResetChallengeController::CACHE_PREFIX.$challengeId);
        }

        return redirect()->route('password.request')->withErrors([
            'email' => 'The authentication code is invalid or has expired. Request a new code.',
        ]);
    }
}
