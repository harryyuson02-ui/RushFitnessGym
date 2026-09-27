<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#18181b">
    <title>@yield('title', 'Administration') · Rush Fitness Gym</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-100 font-sans text-zinc-900 antialiased">
    @php
        $navigation = [
            ['label' => 'Overview', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'M3 13h8V3H3v10Zm0 8h8v-6H3v6Zm10 0h8V11h-8v10Zm0-18v6h8V3h-8Z'],
            ['label' => 'Members', 'route' => 'admin.members.index', 'active' => 'admin.members.*', 'icon' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m7-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm13 10v-2a4 4 0 0 0-3-3.87m-2-11.26a4 4 0 0 1 0 7.75'],
            ['label' => 'Coaches', 'route' => 'admin.coaches.index', 'active' => 'admin.coaches.*', 'icon' => 'M6.5 6.5h11m-13 0H2v5h2.5m15-5H22v5h-2.5M6.5 4v5m11-5v5M8 6.5h8v11H8v-11Zm-1.5 11h11m-13 0H2v-5h2.5m15 5H22v-5h-2.5M6.5 15v5m11-5v5'],
            ['label' => 'Plans', 'route' => 'admin.plans.index', 'active' => 'admin.plans.*', 'icon' => 'M20 12V8H4v4m16 0v8H4v-8m16 0H4m3 4h4'],
        ];
    @endphp

    <div class="min-h-screen lg:grid lg:grid-cols-[16rem_minmax(0,1fr)]">
        <aside class="hidden border-r border-zinc-800 bg-zinc-950 text-white lg:sticky lg:top-0 lg:flex lg:h-screen lg:flex-col">
            <div class="border-b border-zinc-800 px-7 py-7">
                <a href="{{ route('admin.dashboard') }}" class="block" aria-label="Rush Fitness Gym dashboard">
                    <span class="block text-2xl font-black tracking-[0.16em] text-lime-400">RUSH</span>
                    <span class="mt-1 block text-[0.65rem] font-semibold uppercase tracking-[0.26em] text-zinc-400">Fitness Gym</span>
                </a>
            </div>

            <div class="px-4 py-6">
                <p class="px-3 text-[0.65rem] font-bold uppercase tracking-[0.18em] text-zinc-600">Administration</p>
                <nav class="mt-3 grid gap-1" aria-label="Primary navigation">
                    @foreach ($navigation as $item)
                        <a href="{{ route($item['route']) }}" class="flex min-h-11 items-center gap-3 rounded-lg border-l-2 px-3 text-sm font-semibold transition {{ request()->routeIs($item['active']) ? 'border-lime-400 bg-zinc-900 text-white' : 'border-transparent text-zinc-400 hover:bg-zinc-900/70 hover:text-zinc-100' }}">
                            <svg class="size-5 {{ request()->routeIs($item['active']) ? 'text-lime-400' : 'text-zinc-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" /></svg>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="mt-auto border-t border-zinc-800 p-4">
                <div class="mb-3 flex items-center gap-3 px-3 py-2">
                    <div class="flex size-9 items-center justify-center rounded-full bg-lime-400 text-sm font-black text-zinc-950">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <div class="min-w-0"><p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p><p class="text-xs text-zinc-500">Administrator</p></div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="min-h-11 w-full rounded-lg border border-zinc-700 px-4 text-sm font-semibold text-zinc-300 transition hover:border-zinc-500 hover:bg-zinc-900 hover:text-white" type="submit">Sign out</button>
                </form>
            </div>
        </aside>

        <div class="min-w-0 pb-24 lg:pb-0">
            <header class="sticky top-0 z-20 border-b border-zinc-200 bg-white/95 backdrop-blur">
                <div class="mx-auto flex h-16 max-w-[90rem] items-center justify-between px-4 sm:px-6 lg:px-10">
                    <div class="flex items-center gap-3">
                        <div class="lg:hidden"><span class="text-lg font-black tracking-[0.12em]">RUSH</span><span class="ml-2 text-[0.65rem] font-bold uppercase tracking-[0.18em] text-lime-600">Admin</span></div>
                        <div class="hidden lg:block"><p class="text-xs font-semibold uppercase tracking-[0.14em] text-zinc-400">Rush Fitness Gym</p><p class="text-sm font-bold text-zinc-700">Administration Portal</p></div>
                    </div>
                    <div class="flex items-center gap-3"><span class="hidden text-sm font-medium text-zinc-500 sm:inline">{{ now()->format('l, M j') }}</span><span class="size-2 rounded-full bg-lime-500" title="System online"></span></div>
                </div>
            </header>

            <main class="mx-auto w-full max-w-[90rem] p-4 sm:p-6 lg:p-10">
                @if (session('success'))
                    <div class="mb-6 flex items-start gap-3 rounded-lg border border-lime-200 bg-lime-50 px-4 py-3 text-sm font-medium text-lime-900"><span class="mt-0.5 text-lime-600">✓</span><p>{{ session('success') }}</p></div>
                @endif
                @if (session('error'))
                    <div class="mb-6 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800"><span class="mt-0.5">!</span><p>{{ session('error') }}</p></div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <nav class="fixed inset-x-0 bottom-0 z-30 grid grid-cols-4 border-t border-zinc-200 bg-white/95 px-2 pb-[max(.5rem,env(safe-area-inset-bottom))] pt-2 shadow-[0_-4px_20px_rgba(0,0,0,.06)] backdrop-blur lg:hidden" aria-label="Mobile navigation">
        @foreach ($navigation as $item)
            <a href="{{ route($item['route']) }}" class="flex min-h-14 flex-col items-center justify-center gap-1 rounded-lg px-1 text-[0.65rem] font-semibold {{ request()->routeIs($item['active']) ? 'bg-zinc-950 text-white' : 'text-zinc-500' }}">
                <svg class="size-5 {{ request()->routeIs($item['active']) ? 'text-lime-400' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" /></svg>
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>
</body>
</html>
