<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestPasswordResetCodeRequest;
use App\Models\User;
use App\Notifications\PasswordResetCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class PasswordResetChallengeController extends Controller
{
    public const CACHE_PREFIX = 'password-reset:challenge:';

    public const SESSION_KEY = 'password_reset.challenge';

    public const VERIFIED_USER_KEY = 'password_reset.verified_user_id';

    public const VERIFIED_AT_KEY = 'password_reset.verified_at';

    public const EXPIRATION_MINUTES = 10;

    public const MAX_ATTEMPTS = 5;

    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(RequestPasswordResetCodeRequest $request): RedirectResponse
    {
        $email = $request->validated('email');
        $user = User::query()
            ->where('email', $email)
            ->where('role', 'user')
            ->whereHas('member')
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
        ], $expiresAt);

        $request->session()->put(self::SESSION_KEY, $challengeId);
        $request->session()->forget([self::VERIFIED_USER_KEY, self::VERIFIED_AT_KEY]);

        if ($user !== null) {
            try {
                $user->notify(new PasswordResetCode($code));
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return redirect()->route('password.code')->with(
            'status',
            'If a member account matches that email, an authentication code was sent to its email address.',
        );
    }
}
