<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if ($this->verifiedUser() === null) {
            return redirect()->route('password.request')->withErrors([
                'email' => 'Verify an authentication code before choosing a new password.',
            ]);
        }

        return view('auth.reset-password');
    }

    public function store(ResetPasswordRequest $request): RedirectResponse
    {
        $user = $this->verifiedUser();

        if ($user === null) {
            return redirect()->route('password.request')->withErrors([
                'email' => 'Verify an authentication code before choosing a new password.',
            ]);
        }

        $user->forceFill([
            'password' => $request->validated('password'),
        ])->setRememberToken(Str::random(60));
        $user->save();

        event(new PasswordReset($user));

        $request->session()->forget([
            PasswordResetChallengeController::VERIFIED_USER_KEY,
            PasswordResetChallengeController::VERIFIED_AT_KEY,
        ]);
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Your password has been changed. You can now sign in.');
    }

    private function verifiedUser(): ?User
    {
        $userId = session(PasswordResetChallengeController::VERIFIED_USER_KEY);
        $verifiedAt = session(PasswordResetChallengeController::VERIFIED_AT_KEY);

        if (! is_int($userId) || ! is_int($verifiedAt) || $verifiedAt < now()->subMinutes(10)->timestamp) {
            return null;
        }

        return User::query()->whereIn('role', ['admin', 'coach', 'user'])->find($userId);
    }
}
