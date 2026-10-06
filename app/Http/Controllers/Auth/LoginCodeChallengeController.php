<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestLoginCodeRequest;
use App\Models\User;
use App\Notifications\LoginVerificationCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

class LoginCodeChallengeController extends Controller
{
    public const CACHE_PREFIX = 'login-code:challenge:';

    public const SESSION_KEY = 'login_code.challenge';

    public const EXPIRATION_MINUTES = 10;

    public const MAX_ATTEMPTS = 5;

    public function store(RequestLoginCodeRequest $request): RedirectResponse
    {
        $user = User::query()
            ->where('email', $request->validated('email'))
            ->whereIn('role', ['admin', 'coach', 'user'])
            ->first();
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $challengeId = (string) Str::uuid();
        $expiresAt = now()->addMinutes(self::EXPIRATION_MINUTES);
        $previousChallengeId = $request->session()->get(self::SESSION_KEY);

        if (is_string($previousChallengeId)) {
            Cache::forget(self::CACHE_PREFIX.$previousChallengeId);
        }

        Cache::put(self::CACHE_PREFIX.$challengeId, [
            'user_id' => $user?->id,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => $expiresAt->timestamp,
            'remember' => $request->boolean('remember'),
        ], $expiresAt);

        $request->session()->put(self::SESSION_KEY, $challengeId);

        if ($user !== null) {
            try {
                $user->notify(new LoginVerificationCode($code));
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return redirect()->route('login.code')->with(
            'status',
            'If an account matches that email, a verification code was sent to its email address.',
        );
    }
}
