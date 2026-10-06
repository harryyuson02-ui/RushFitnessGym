<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyLoginCodeRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LoginCodeVerificationController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($this->challenge($request) === null) {
            $request->session()->forget(LoginCodeChallengeController::SESSION_KEY);

            return redirect()->route('login')->withErrors([
                'email' => 'Request a new verification code to continue.',
            ]);
        }

        return view('auth.verify-login-code');
    }

    public function store(VerifyLoginCodeRequest $request): RedirectResponse
    {
        $challenge = $this->challenge($request);
        $challengeId = $request->session()->get(LoginCodeChallengeController::SESSION_KEY);

        if ($challenge === null || ! is_string($challengeId)) {
            return $this->expiredResponse($request);
        }

        $isValid = Hash::check($request->validated('code'), $challenge['code_hash']);
        $user = $isValid && is_int($challenge['user_id'])
            ? User::query()->whereIn('role', ['admin', 'coach', 'user'])->find($challenge['user_id'])
            : null;

        if ($user === null) {
            $challenge['attempts']++;

            if ($challenge['attempts'] >= LoginCodeChallengeController::MAX_ATTEMPTS) {
                return $this->expiredResponse($request);
            }

            Cache::put(
                LoginCodeChallengeController::CACHE_PREFIX.$challengeId,
                $challenge,
                now()->addSeconds(max(1, $challenge['expires_at'] - now()->timestamp)),
            );

            return back()->withErrors(['code' => 'The verification code is invalid or has expired.']);
        }

        Cache::forget(LoginCodeChallengeController::CACHE_PREFIX.$challengeId);
        Auth::login($user, $challenge['remember']);
        $request->session()->regenerate();
        $request->session()->forget(LoginCodeChallengeController::SESSION_KEY);

        $route = match ($user->role) {
            'admin' => 'admin.dashboard',
            'coach' => 'coach.dashboard',
            'user' => 'member.dashboard',
        };

        return redirect()->intended(route($route));
    }

    /**
     * @return array{user_id: int|null, code_hash: string, attempts: int, expires_at: int, remember: bool}|null
     */
    private function challenge(Request $request): ?array
    {
        $challengeId = $request->session()->get(LoginCodeChallengeController::SESSION_KEY);

        if (! is_string($challengeId)) {
            return null;
        }

        $challenge = Cache::get(LoginCodeChallengeController::CACHE_PREFIX.$challengeId);

        if (! is_array($challenge) || $challenge['expires_at'] < now()->timestamp) {
            return null;
        }

        return $challenge;
    }

    private function expiredResponse(VerifyLoginCodeRequest $request): RedirectResponse
    {
        $challengeId = $request->session()->pull(LoginCodeChallengeController::SESSION_KEY);

        if (is_string($challengeId)) {
            Cache::forget(LoginCodeChallengeController::CACHE_PREFIX.$challengeId);
        }

        return redirect()->route('login')->withErrors([
            'email' => 'The verification code is invalid or has expired. Request a new code.',
        ]);
    }
}
