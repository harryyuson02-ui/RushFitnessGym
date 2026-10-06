<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#99e600">
    <title>Verify sign-in code · Rush Fitness Gym</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-zinc-100 font-sans text-zinc-950 antialiased">
    <main class="flex min-h-dvh items-center justify-center p-4 sm:p-8">
        <section class="rush-card w-full max-w-md px-6 py-9 sm:p-10" aria-labelledby="verify-login-code-title">
            <a href="{{ route('welcome') }}" class="mb-8 inline-block rounded-lg" aria-label="Return to the Rush Fitness Gym welcome page">
                <span class="block text-4xl font-black tracking-[0.16em] text-lime-600">RUSH</span>
                <span class="mt-1 block text-xs font-semibold tracking-[0.26em] text-zinc-500">FITNESS GYM</span>
            </a>
            <p class="text-[0.65rem] font-bold uppercase tracking-[0.18em] text-lime-700">Secure sign in</p>
            <h1 id="verify-login-code-title" class="mt-2 text-3xl font-bold tracking-tight">Enter your verification code</h1>
            <p class="mt-2 text-sm leading-6 text-zinc-500">Enter the six-digit code sent to your email address. It expires in 10 minutes.</p>

            @if (session('status'))
                <div role="status" class="mt-6 rounded-lg border border-lime-200 bg-lime-50 p-4 text-sm text-lime-900">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div role="alert" class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.code.verify') }}" class="mt-8 grid gap-5">
                @csrf
                <label class="rush-label">Verification code<input class="rush-input text-center text-2xl tracking-[0.35em]" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" minlength="6" maxlength="6" required autofocus></label>
                <button class="rush-primary" type="submit">Verify and sign in</button>
                <a href="{{ route('login') }}" class="rush-secondary text-center">Request a new code</a>
            </form>
        </section>
    </main>
</body>
</html>
