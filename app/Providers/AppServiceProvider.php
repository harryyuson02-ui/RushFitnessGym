<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('password-reset-code', function (Request $request): array {
            $email = Str::lower($request->string('email')->toString());

            return [
                Limit::perMinute(3)->by('password-reset-ip:'.$request->ip()),
                Limit::perHour(5)->by('password-reset-email:'.$email),
            ];
        });

        RateLimiter::for('password-reset-verify', fn (Request $request): Limit => Limit::perMinute(10)->by(
            'password-reset-verify:'.($request->session()->get('password_reset.challenge') ?? $request->ip()),
        ));

        View::composer('layouts.portal', function ($view): void {
            $user = auth()->user();
            $notifications = $user?->exists
                ? $user->unreadNotifications()->latest()->limit(6)->get()
                : collect();

            $view->with('portalNotifications', $notifications);
        });
    }
}
