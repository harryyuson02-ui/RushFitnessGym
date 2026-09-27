<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rush Fitness Gym</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    @vite('resources/css/app.css')
</head>
<body class="min-h-screen bg-zinc-950 text-white antialiased">
    <main class="grid min-h-screen lg:grid-cols-[1.1fr_.9fr]">
        <section class="relative hidden overflow-hidden p-12 lg:flex lg:flex-col lg:justify-between">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(163,230,53,.18),transparent_38%),linear-gradient(135deg,#09090b,#27272a)]"></div>
            <div class="relative">
                <p class="font-['Anton'] text-5xl tracking-[0.18em] text-lime-400">RUSH</p>
                <p class="text-xs font-bold tracking-[0.35em] text-zinc-400">FITNESS GYM</p>
            </div>
            <div class="relative max-w-lg">
                <p class="mb-5 text-sm font-bold uppercase tracking-[0.2em] text-lime-400">Admin operations</p>
                <h1 class="text-5xl font-bold leading-tight tracking-tight">Every member.<br>One clear workflow.</h1>
                <p class="mt-5 max-w-md leading-7 text-zinc-400">Onboard clients, assign coaches, track subscriptions, and issue QR access keys from one secure desk.</p>
            </div>
        </section>

        <section class="flex min-h-screen items-center justify-center bg-zinc-100 p-5 text-zinc-950 sm:p-10">
            <div class="w-full max-w-md rounded-xl border border-zinc-200 bg-white p-6 shadow-xl shadow-black/10 sm:p-10">
                <div class="mb-8 lg:hidden">
                    <p class="font-['Anton'] text-4xl tracking-[0.18em] text-lime-500">RUSH</p>
                    <p class="text-xs font-bold tracking-[0.25em] text-zinc-500">FITNESS GYM</p>
                </div>

                <p class="text-xs font-bold uppercase tracking-[0.18em] text-lime-600">Authorized access only</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight">Admin sign in</h2>
                <p class="mt-2 text-sm leading-relaxed text-zinc-500">Member registration is completed in person by an administrator.</p>

                @if ($errors->any())
                    <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}" class="mt-7 grid gap-5">
                    @csrf
                    <label class="grid gap-2 text-sm font-bold" for="email">
                        Email address
                        <input class="min-h-12 rounded-lg border border-zinc-300 bg-white px-4 font-normal outline-none focus:border-lime-500 focus:ring-4 focus:ring-lime-100" type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
                    </label>
                    <label class="grid gap-2 text-sm font-bold" for="password">
                        Password
                        <input class="min-h-12 rounded-lg border border-zinc-300 bg-white px-4 font-normal outline-none focus:border-lime-500 focus:ring-4 focus:ring-lime-100" type="password" id="password" name="password" autocomplete="current-password" required>
                    </label>
                    <label class="flex min-h-11 items-center gap-3 text-sm text-zinc-600">
                        <input class="size-5 accent-lime-500" type="checkbox" name="remember" value="1">
                        Keep me signed in on this device
                    </label>
                    <button class="min-h-12 rounded-lg bg-lime-400 px-5 font-bold text-zinc-950 transition hover:bg-lime-300" type="submit">Sign in securely</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
