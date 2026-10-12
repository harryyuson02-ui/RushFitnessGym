<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#99e600">
    <title>Choose a new password · Rush Fitness Gym</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-zinc-100 font-sans text-zinc-950 antialiased">
    <main class="flex min-h-dvh items-center justify-center p-4 sm:p-8">
        <section class="rush-card w-full max-w-md px-6 py-9 sm:p-10" aria-labelledby="reset-password-title">
            <a href="{{ route('welcome') }}" class="mb-8 inline-block rounded-lg" aria-label="Return to the Rush Fitness Gym welcome page">
                <span class="block text-4xl font-black tracking-[0.16em] text-lime-600">RUSH</span>
                <span class="mt-1 block text-xs font-semibold tracking-[0.26em] text-zinc-500">FITNESS GYM</span>
            </a>
            <p class="text-[0.65rem] font-bold uppercase tracking-[0.18em] text-lime-700">Secure password reset</p>
            <h1 id="reset-password-title" class="mt-2 text-3xl font-bold tracking-tight">Choose a new password</h1>
            <p class="mt-2 text-sm leading-6 text-zinc-500">Use at least eight characters with a letter and number.</p>

            @if ($errors->any())
                <div role="alert" class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="mt-8 grid gap-5">
                @csrf
                <label class="rush-label">New password<span class="relative"><input id="reset-password" class="rush-input pr-14" type="password" name="password" autocomplete="new-password" required autofocus><button type="button" data-password-toggle aria-controls="reset-password" aria-label="Show new password" aria-pressed="false" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-lg text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-950"><svg class="size-5" data-password-show viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg><svg class="size-5" data-password-hide viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" hidden><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 6.2A10.8 10.8 0 0 1 12 6c6 0 9.5 6 9.5 6a17 17 0 0 1-2.2 2.8M6.6 6.6C4 8.4 2.5 12 2.5 12s3.5 6 9.5 6a9.8 9.8 0 0 0 3.2-.5M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg></button></span></label>
                <label class="rush-label">Confirm new password<span class="relative"><input id="reset-password-confirmation" class="rush-input pr-14" type="password" name="password_confirmation" autocomplete="new-password" required><button type="button" data-password-toggle aria-controls="reset-password-confirmation" aria-label="Show password confirmation" aria-pressed="false" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-lg text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-950"><svg class="size-5" data-password-show viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg><svg class="size-5" data-password-hide viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" hidden><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 6.2A10.8 10.8 0 0 1 12 6c6 0 9.5 6 9.5 6a17 17 0 0 1-2.2 2.8M6.6 6.6C4 8.4 2.5 12 2.5 12s3.5 6 9.5 6a9.8 9.8 0 0 0 3.2-.5M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg></button></span></label>
                <button class="rush-primary" type="submit">Change password</button>
            </form>
        </section>
    </main>
</body>
</html>
