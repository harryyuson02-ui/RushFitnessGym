<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#99e600">
    <title>Verify authentication code · Rush Fitness Gym</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-zinc-100 font-sans text-zinc-950 antialiased">
    <main class="flex min-h-dvh items-center justify-center p-4 sm:p-8">
        <section class="rush-card w-full max-w-md px-6 py-9 sm:p-10" aria-labelledby="verify-code-title">
            <a href="{{ route('welcome') }}" class="mb-8 inline-block rounded-lg" aria-label="Return to the Rush Fitness Gym welcome page">
                <span class="block text-4xl font-black tracking-[0.16em] text-lime-600">RUSH</span>
                <span class="mt-1 block text-xs font-semibold tracking-[0.26em] text-zinc-500">FITNESS GYM</span>
            </a>
            <p class="text-[0.65rem] font-bold uppercase tracking-[0.18em] text-lime-700">Email verification</p>
            <h1 id="verify-code-title" class="mt-2 text-3xl font-bold tracking-tight">Enter authentication code</h1>
            <p class="mt-2 text-sm leading-6 text-zinc-500">Enter the six-digit code sent to your email address.</p>

            @if (session('status'))
                <div role="status" class="mt-6 rounded-lg border border-lime-200 bg-lime-50 p-4 text-sm text-lime-900">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div role="alert" class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.verify-code') }}" class="mt-8 grid gap-5">
                @csrf
                @php($codeDigits = old('code_digits', str_split((string) old('code', ''))))
                <fieldset class="grid gap-2" data-verification-code>
                    <legend class="text-sm font-bold">Authentication code</legend>
                    <div class="grid grid-cols-6 gap-2" role="group" aria-label="Six-digit authentication code">
                        @for ($index = 0; $index < 6; $index++)
                            <input
                                class="min-h-14 min-w-0 rounded-xl border border-zinc-300 bg-white text-center text-2xl font-bold tabular-nums outline-none transition focus:border-lime-500 focus:ring-4 focus:ring-lime-100"
                                type="text"
                                name="code_digits[]"
                                value="{{ $codeDigits[$index] ?? '' }}"
                                inputmode="numeric"
                                pattern="[0-9]"
                                maxlength="1"
                                aria-label="Code digit {{ $index + 1 }} of 6"
                                data-code-digit
                                @if ($index === 0) autocomplete="one-time-code" autofocus @else autocomplete="off" @endif
                                required
                            >
                        @endfor
                    </div>
                    <p class="text-xs text-zinc-500">Type or paste the six-digit code from your email.</p>
                </fieldset>
                <button class="rush-primary" type="submit">Verify code</button>
                <a href="{{ route('password.request') }}" class="rush-secondary text-center">Request a new code</a>
            </form>
        </section>
    </main>
</body>
</html>
