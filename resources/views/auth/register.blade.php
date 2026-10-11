<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#99e600">
    <title>Create member account · Rush Fitness Gym</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-zinc-100 font-sans text-zinc-950 antialiased">
    <main class="flex min-h-dvh items-center justify-center p-4 sm:p-8">
        <section class="rush-card w-full max-w-md px-6 py-9 sm:p-10" aria-labelledby="register-title">
            <a href="{{ route('welcome') }}" class="mb-8 inline-block rounded-lg" aria-label="Return to the Rush Fitness Gym welcome page">
                <span class="block text-4xl font-black tracking-[0.16em] text-lime-600">RUSH</span>
                <span class="mt-1 block text-xs font-semibold tracking-[0.26em] text-zinc-500">FITNESS GYM</span>
            </a>
            <p class="text-[0.65rem] font-bold uppercase tracking-[0.18em] text-lime-700">New client registration</p>
            <h1 id="register-title" class="mt-2 text-3xl font-bold tracking-tight">Create your account</h1>
            <p class="mt-2 text-sm leading-6 text-zinc-500">Set up secure portal access, then complete your membership application.</p>

            <form method="POST" action="{{ route('register.store') }}" class="mt-8 grid gap-5">
                @csrf
                <label class="rush-label">Full name<input class="rush-input" name="name" value="{{ old('name') }}" autocomplete="name" required autofocus><x-input-error :messages="$errors->get('name')" /></label>
                <label class="rush-label">Email address<input class="rush-input" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required><x-input-error :messages="$errors->get('email')" /></label>
                <label class="rush-label">Password<span class="relative"><input id="register-password" class="rush-input pr-14" type="password" name="password" autocomplete="new-password" required><button type="button" data-password-toggle aria-controls="register-password" aria-label="Show password" aria-pressed="false" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-lg text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-950"><svg class="size-5" data-password-show viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg><svg class="size-5" data-password-hide viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" hidden><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 6.2A10.8 10.8 0 0 1 12 6c6 0 9.5 6 9.5 6a17 17 0 0 1-2.2 2.8M6.6 6.6C4 8.4 2.5 12 2.5 12s3.5 6 9.5 6a9.8 9.8 0 0 0 3.2-.5M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg></button></span><span class="text-xs font-normal text-zinc-500">Use at least 8 characters with a letter and number.</span><x-input-error :messages="$errors->get('password')" /></label>
                <label class="rush-label">Confirm password<span class="relative"><input id="register-password-confirmation" class="rush-input pr-14" type="password" name="password_confirmation" autocomplete="new-password" required><button type="button" data-password-toggle aria-controls="register-password-confirmation" aria-label="Show password confirmation" aria-pressed="false" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-lg text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-950"><svg class="size-5" data-password-show viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg><svg class="size-5" data-password-hide viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" hidden><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 6.2A10.8 10.8 0 0 1 12 6c6 0 9.5 6 9.5 6a17 17 0 0 1-2.2 2.8M6.6 6.6C4 8.4 2.5 12 2.5 12s3.5 6 9.5 6a9.8 9.8 0 0 0 3.2-.5M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg></button></span></label>
                <button class="rush-primary mt-1" type="submit">Create member account</button>
                <a href="{{ route('login') }}" class="rush-secondary text-center">Back to sign in</a>
            </form>
        </section>
    </main>
</body>
</html>
