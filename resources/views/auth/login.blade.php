<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#99e600">
    <title>Portal sign in · Rush Fitness Gym</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-zinc-100 font-sans text-zinc-950 antialiased">
    <main class="flex min-h-dvh items-center justify-center p-4 sm:p-8">
        <section class="rush-card w-full max-w-md px-6 py-9 sm:p-10" aria-labelledby="sign-in-title">
            <a href="{{ route('welcome') }}" class="mb-10 inline-block rounded-lg" aria-label="Return to the Rush Fitness Gym welcome page">
                <span class="block text-4xl font-black tracking-[0.16em] text-lime-600">RUSH</span>
                <span class="mt-1 block text-xs font-semibold tracking-[0.26em] text-zinc-500">FITNESS GYM</span>
            </a>
            <p class="text-[0.65rem] font-bold uppercase tracking-[0.18em] text-lime-700">Authorized access only</p>
            <h1 id="sign-in-title" class="mt-2 text-3xl font-bold tracking-tight">Portal sign in</h1>
            <p class="mt-2 text-sm leading-6 text-zinc-500">Access your administrator, coach, or member workspace.</p>
            @if (session('status'))
                <div role="status" class="mt-6 rounded-lg border border-lime-200 bg-lime-50 p-4 text-sm text-lime-900">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div role="alert" class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('login.submit') }}" class="mt-9 grid gap-6">
                @csrf
                <div class="grid gap-2">
                    <label class="text-sm font-semibold" for="email">Email address</label>
                    <input class="rush-input" type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
                </div>
                <div class="grid gap-2">
                    <div class="flex items-center justify-between gap-3">
                        <label class="text-sm font-semibold" for="password">Password</label>
                        <a href="{{ route('password.request') }}" class="text-sm font-semibold text-lime-700 hover:text-lime-800">Forgot password?</a>
                    </div>
                    <div class="relative">
                        <input class="rush-input pr-14" type="password" id="password" name="password" autocomplete="current-password" required>
                        <button type="button" data-password-toggle aria-controls="password" aria-label="Show password" aria-pressed="false" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-lg text-zinc-500 hover:text-zinc-950">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
                <label class="flex min-h-11 items-center gap-3 text-sm text-zinc-600">
                    <input class="size-5 shrink-0 accent-lime-500" type="checkbox" name="remember" value="1" @checked(old('remember'))>
                    Keep me signed in on this device
                </label>
                <button class="rush-primary" type="submit">Sign in securely</button>
                <div class="flex items-center gap-3" aria-hidden="true">
                    <span class="h-px flex-1 bg-zinc-200"></span>
                    <span class="text-xs font-semibold uppercase tracking-wider text-zinc-400">or</span>
                    <span class="h-px flex-1 bg-zinc-200"></span>
                </div>
                <div class="grid gap-2">
                    <button class="rush-secondary" type="submit" formaction="{{ route('login.code.send') }}" formnovalidate>Email me a verification code</button>
                    <p class="text-center text-xs leading-5 text-zinc-500">We’ll send a six-digit code to the email address above. No password is required.</p>
                </div>
                <a href="{{ route('register') }}" class="rush-secondary text-center">Create account</a>
            </form>
            <p class="mt-7 flex items-center justify-center gap-2 text-xs leading-5 text-zinc-500">
                <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 5v2"/></svg>
                Secure access for Rush Fitness clients and staff.
            </p>
        </section>
    </main>
</body>
</html>
