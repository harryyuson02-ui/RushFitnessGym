<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#99e600">
    <meta name="description" content="Welcome to Rush Fitness Gym.">
    <title>Welcome · Rush Fitness Gym</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-zinc-950 font-sans text-white antialiased">
    <main class="relative isolate flex min-h-dvh items-center justify-center overflow-hidden px-5 py-12">
        <div class="absolute inset-0 -z-20 bg-[radial-gradient(circle_at_50%_10%,rgba(153,230,0,0.18),transparent_38%),linear-gradient(145deg,#18181b_0%,#09090b_62%)]" aria-hidden="true"></div>
        <div class="absolute -left-24 top-1/3 -z-10 size-72 rounded-full border border-lime-400/10" aria-hidden="true"></div>
        <div class="absolute -right-32 bottom-10 -z-10 size-96 rounded-full border border-white/5" aria-hidden="true"></div>

        <section class="w-full max-w-3xl text-center" aria-labelledby="welcome-title">
            <div class="mx-auto flex size-20 items-center justify-center rounded-3xl bg-rush text-zinc-950 shadow-[0_0_60px_rgba(153,230,0,0.22)] sm:size-24">
                <svg class="size-10 sm:size-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M6.5 6.5h11v11h-11zM3 9v6m18-6v6M9 3v3m6-3v3M9 18v3m6-3v3"/>
                </svg>
            </div>

            <p class="mt-8 text-sm font-black uppercase tracking-[0.32em] text-rush">Rush Fitness Gym</p>
            <h1 id="welcome-title" class="mt-4 text-5xl font-black tracking-[-0.045em] sm:text-7xl">Welcome to Rush</h1>
            <p class="mx-auto mt-5 max-w-xl text-base leading-7 text-zinc-400 sm:text-lg">Your fitness journey, coaching schedule, membership, and gym community are ready in one connected portal.</p>

            <a href="{{ route('login') }}" class="rush-primary mt-10 min-w-52 px-8">
                Continue to login
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </a>
        </section>
    </main>
</body>
</html>
