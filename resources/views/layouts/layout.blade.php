<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Rush Fitness Gym')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Anton&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <style>

        /* ==============================
           RESET
        ============================== */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        /* ==============================
           BODY
        ============================== */

        body {
            font-family: 'Inter', sans-serif;
            background: #f5f5f5;
            color: #1e1e1e;
        }


        /* ==============================
           PAGE LAYOUT
        ============================== */

        .page-container {
            display: flex;
            min-height: 100vh;
        }


        /* ==============================
           SIDEBAR
        ============================== */

        .sidebar {
            width: 250px;
            background: #1e1e1e;
            color: #fff;
            padding: 25px 20px;

            display: flex;
            flex-direction: column;

            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
        }


        /* LOGO */

        .sidebar-logo {
            text-align: center;
            margin-bottom: 40px;
        }

        .sidebar-logo h2 {
            font-family: 'Anton', sans-serif;
            font-size: 32px;
            letter-spacing: 3px;
            color: #24f404;
        }

        .sidebar-logo span {
            display: block;
            font-size: 11px;
            letter-spacing: 3px;
            color: #fff;
        }


        /* SIDEBAR NAVIGATION */

        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .sidebar-nav a {
            display: block;

            padding: 13px 15px;

            text-decoration: none;
            color: #aaa;

            border-radius: 8px;

            transition: 0.2s;
        }

        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: #3ad222;
            color: #1e1e1e;
        }


        /* LOGOUT */

        .sidebar-bottom {
            margin-top: auto;
        }

        .sidebar-bottom button,
        .sidebar-bottom a {
            display: block;
            width: 100%;

            padding: 13px 15px;

            text-align: center;
            text-decoration: none;

            color: #aaa;
            background: transparent;

            border: 1px solid #444;
            border-radius: 8px;

            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-size: 14px;

            transition: 0.2s;
        }

        .sidebar-bottom button:hover,
        .sidebar-bottom a:hover {
            background: #3ad222;
            color: #1e1e1e;
        }


        /* ==============================
           MAIN CONTENT
        ============================== */

        .main-content {
            width: calc(100% - 250px);

            margin-left: 250px;

            padding: 40px;
        }


        /* ==============================
           PAGE HEADER
        ============================== */

        .page-header {
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 30px;
            margin-bottom: 8px;
        }

        .page-header p {
            color: #777;
        }


        /* ==============================
           STATISTICS
        ============================== */

        .stats-grid {
            display: grid;

            grid-template-columns: repeat(4, 1fr);

            gap: 20px;

            margin-bottom: 30px;
        }

        .stat-card {
            background: #fff;

            padding: 25px;

            border-radius: 12px;

            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
        }

        .stat-card h3 {
            font-size: 14px;
            color: #777;

            margin-bottom: 12px;
        }

        .stat-card p {
            font-size: 28px;
            font-weight: 700;
        }


        /* ==============================
           CONTENT CARD
        ============================== */

        .content-card {
            background: #fff;

            padding: 25px;

            margin-bottom: 25px;

            border-radius: 12px;

            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
        }

        .content-card h2 {
            margin-bottom: 20px;
        }

        .card-header {
            display: flex;

            justify-content: space-between;
            align-items: center;

            margin-bottom: 20px;
        }


        /* ==============================
           TABLE
        ============================== */

        .data-table {
            width: 100%;

            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 15px;

            text-align: left;

            border-bottom: 1px solid #eee;
        }

        .data-table th {
            font-size: 13px;
            color: #777;
        }

        .data-table td {
            font-size: 14px;
        }


        /* ==============================
           BUTTON
        ============================== */

        .primary-btn {
            background: #3ad222;

            border: none;

            padding: 10px 18px;

            border-radius: 7px;

            cursor: pointer;

            font-weight: 600;
        }

        .primary-btn:hover {
            opacity: 0.85;
        }


        /* ==============================
           RESPONSIVE - TABLET
        ============================== */

        @media (max-width: 900px) {

            .sidebar {
                width: 200px;
            }

            .main-content {
                width: calc(100% - 200px);

                margin-left: 200px;

                padding: 25px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }


        /* ==============================
           RESPONSIVE - MOBILE
        ============================== */

        @media (max-width: 600px) {

            .page-container {
                flex-direction: column;
            }

            .sidebar {
                position: relative;

                width: 100%;

                min-height: auto;
            }

            .main-content {
                width: 100%;

                margin-left: 0;

                padding: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

    </style>
</head>


<body class="min-h-screen bg-zinc-100 text-zinc-950 antialiased">
    @php
        $navigation = [
            ['label' => 'Home', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
            ['label' => 'Members', 'route' => 'admin.members.index', 'active' => 'admin.members.*'],
            ['label' => 'Coaches', 'route' => 'admin.coaches.index', 'active' => 'admin.coaches.*'],
            ['label' => 'Plans', 'route' => 'admin.plans.index', 'active' => 'admin.plans.*'],
        ];
    @endphp

    <div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">
        <aside class="hidden bg-zinc-950 p-6 text-white lg:flex lg:min-h-screen lg:flex-col">
            <a href="{{ route('admin.dashboard') }}" class="block">
                <span class="font-['Anton'] text-4xl tracking-[0.18em] text-lime-400">RUSH</span>
                <span class="mt-1 block text-xs font-bold tracking-[0.28em] text-zinc-400">FITNESS GYM</span>
            </a>

            <nav class="mt-12 grid gap-2">
                @foreach ($navigation as $item)
                    <a href="{{ route($item['route']) }}" class="rounded-xl px-4 py-3 font-semibold transition {{ request()->routeIs($item['active']) ? 'bg-lime-400 text-zinc-950' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <form method="POST" action="{{ route('logout') }}" class="mt-auto">
                @csrf
                <button class="min-h-12 w-full rounded-xl border border-zinc-700 px-4 font-semibold text-zinc-300">Sign out</button>
            </form>
        </aside>

        <div class="min-w-0 pb-24 lg:pb-0">
            <header class="sticky top-0 z-20 flex items-center justify-between border-b border-zinc-200 bg-white/95 px-4 py-3 backdrop-blur lg:px-10 lg:py-5">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-lime-600">Admin desk</p>
                    <p class="font-['Anton'] text-xl uppercase tracking-wide">Rush Fitness</p>
                </div>
                <div class="rounded-full bg-zinc-100 px-3 py-2 text-xs font-bold text-zinc-600">{{ auth()->user()->name }}</div>
            </header>

            <main class="mx-auto w-full max-w-7xl p-4 lg:p-10">
                @if (session('success'))
                    <div class="mb-5 rounded-2xl border border-lime-300 bg-lime-50 p-4 text-sm font-semibold text-lime-900">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800">{{ session('error') }}</div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <nav class="fixed inset-x-0 bottom-0 z-30 grid grid-cols-4 border-t border-zinc-200 bg-white px-2 pb-[max(.5rem,env(safe-area-inset-bottom))] pt-2 shadow-[0_-8px_30px_rgba(0,0,0,.08)] lg:hidden">
        @foreach ($navigation as $item)
            <a href="{{ route($item['route']) }}" class="flex min-h-14 items-center justify-center rounded-xl px-1 text-xs font-bold {{ request()->routeIs($item['active']) ? 'bg-lime-400 text-zinc-950' : 'text-zinc-500' }}">
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>
</body>

</html>
