<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#18181b">
    <title>@yield('title', 'Portal') · Rush Fitness Gym</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-100 font-sans text-zinc-900 antialiased">
@php
    $portalUser = auth()->user();
    $portalHomeUrl = route(match ($portalUser->role) {
        'admin' => 'admin.dashboard',
        'coach' => 'coach.dashboard',
        default => 'member.dashboard',
    });
    $roleLabel = ['admin' => 'Administration', 'coach' => 'Coach workspace', 'user' => 'Member portal'][$portalUser->role] ?? 'Portal';
    $navigation = match ($portalUser->role) {
        'admin' => [
            ['Overview', 'admin.dashboard', 'admin.dashboard'],
            ['Members', 'admin.members.index', ['admin.members.*', 'admin.users.*']], ['Coaches', 'admin.coaches.index', 'admin.coaches.*'],
            ['Plans', 'admin.plans.index', 'admin.plans.*'],
            ['Approval reviews', 'admin.approvals.index', 'admin.approvals.*'],
            ['Transactions', 'admin.transactions.index', 'admin.transactions.*'],
            ['Chat', 'chat.index', 'chat.*'],
        ],
        'coach' => [
            ['Overview', 'coach.dashboard', 'coach.dashboard'], ['Clients', 'coach.clients.index', 'coach.clients.*'],
            ['Approvals', 'coach.approvals.index', 'coach.approvals.*'],
            ['Availability', 'coach.availability.edit', 'coach.availability.*'],
            ['Chat', 'chat.index', 'chat.*'],
        ],
        default => [
            ['Overview', 'member.dashboard', 'member.dashboard'],
            ['Plans', 'member.plans.index', 'member.plans.*'],
            ['My records', 'member.records.index', 'member.records.*'],
            ['Transactions', 'member.transactions.index', 'member.transactions.*'], ['Chat', 'chat.index', 'chat.*'],
        ],
    };
@endphp
@if ($portalUser->isMember())
    <aside hidden class="fixed right-4 top-20 z-[70] w-[min(24rem,calc(100vw-2rem))] translate-y-3 rounded-2xl border border-lime-300 bg-zinc-950 p-4 text-white opacity-0 shadow-2xl transition duration-300" data-attendance-toast data-notification-url="{{ route('notifications.attendance') }}" data-known-notification-ids='{{ $portalUser->unreadNotifications()->where('type', \App\Notifications\AttendanceCheckedIn::class)->latest()->limit(5)->pluck('id')->toJson() }}' role="status" aria-live="polite">
        <div class="flex items-start gap-3">
            <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-lime-400 text-lg font-black text-zinc-950">✓</span>
            <div class="min-w-0 flex-1"><p class="font-extrabold" data-attendance-toast-title>Attendance recorded</p><p class="mt-1 text-sm leading-5 text-zinc-300" data-attendance-toast-body>Your QR was scanned successfully.</p></div>
            <button type="button" class="rounded-lg px-2 py-1 text-zinc-400 hover:bg-zinc-800 hover:text-white" data-attendance-toast-close aria-label="Dismiss attendance notification">✕</button>
        </div>
    </aside>
@endif
<div class="min-h-screen lg:grid lg:grid-cols-[17rem_minmax(0,1fr)]">
    <aside class="hidden border-r border-zinc-800 bg-zinc-950 text-white lg:sticky lg:top-0 lg:flex lg:h-screen lg:flex-col">
        <div class="border-b border-zinc-800 px-7 py-7"><a href="{{ $portalHomeUrl }}" class="block"><span class="block text-2xl font-black tracking-[0.16em] text-lime-400">RUSH</span><span class="mt-1 block text-[0.65rem] font-semibold uppercase tracking-[0.26em] text-zinc-400">Fitness Gym</span></a></div>
        <nav class="grid gap-1 px-4 py-6" aria-label="Primary navigation">
            <p class="mb-2 px-3 text-[0.65rem] font-bold uppercase tracking-[0.18em] text-zinc-600">{{ $roleLabel }}</p>
            @foreach ($navigation as [$label, $routeName, $active])
                @php($isActive = request()->routeIs(...(array) $active))
                <a href="{{ route($routeName) }}" @if($isActive) aria-current="page" @endif class="flex min-h-11 items-center gap-3 rounded-lg border-l-2 px-3 text-sm font-semibold transition {{ $isActive ? 'border-lime-400 bg-zinc-900 text-white' : 'border-transparent text-zinc-400 hover:bg-zinc-900 hover:text-white' }}"><span class="size-1.5 rounded-full {{ $isActive ? 'bg-lime-400' : 'bg-zinc-700' }}"></span>{{ $label }}</a>
            @endforeach
        </nav>
        <div class="mt-auto border-t border-zinc-800 p-4"><div class="mb-3 flex items-center gap-3 px-3 py-2"><span class="rush-avatar">{{ mb_strtoupper(mb_substr($portalUser->name, 0, 1)) }}</span><div class="min-w-0"><p class="truncate text-sm font-semibold">{{ $portalUser->name }}</p><p class="text-xs capitalize text-zinc-500">{{ $portalUser->role }}</p></div></div><form method="POST" action="{{ route('logout') }}">@csrf<button class="rush-dark-button w-full" type="submit">Sign out</button></form></div>
    </aside>
    <div class="min-w-0 pb-24 lg:pb-0">
        <header class="sticky top-0 z-30 border-b border-zinc-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-[90rem] items-center justify-between gap-3 px-4 sm:px-6 lg:px-10">
                <div><p class="text-xs font-black tracking-[0.14em] text-zinc-950 lg:text-zinc-400">RUSH</p><p class="hidden text-sm font-bold text-zinc-700 lg:block">{{ $roleLabel }}</p></div>
                <div class="flex items-center gap-2">
                    <details class="rush-menu relative"><summary class="relative flex size-11 cursor-pointer items-center justify-center rounded-lg text-zinc-600 hover:bg-zinc-100" aria-label="Notifications"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.8 23.8 0 0 0 5.454-1.31A9 9 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a9 9 0 0 1-2.312 6.022 23.8 23.8 0 0 0 5.455 1.31m5.714 0a3 3 0 1 1-5.714 0"/></svg>@if($portalNotifications->isNotEmpty())<span class="absolute right-1 top-1 min-w-4 rounded-full bg-lime-500 px-1 text-center text-[0.625rem] font-black leading-4">{{ $portalNotifications->count() }}</span>@endif</summary>
                        <div class="absolute right-0 top-full mt-2 w-[min(22rem,calc(100vw-2rem))] rounded-2xl border border-zinc-200 bg-white p-3 shadow-xl"><div class="flex items-center justify-between gap-3"><p class="px-2 py-2 text-sm font-bold">Notifications</p><button type="button" class="rounded-lg px-2 py-1 font-bold text-zinc-500 hover:bg-zinc-100 hover:text-zinc-950" data-menu-close aria-label="Close notifications">✕</button></div><div class="grid max-h-[min(20rem,calc(100dvh-6rem))] gap-2 overflow-y-auto overscroll-contain">@forelse($portalNotifications as $notification)<form method="POST" action="{{ route('notifications.update', $notification) }}">@csrf @method('PATCH')<button type="submit" class="group flex min-h-24 w-full items-center gap-3 rounded-xl bg-zinc-50 p-3 text-left transition hover:bg-lime-50 active:bg-lime-100"><span class="min-w-0 flex-1"><span class="block text-sm font-semibold text-zinc-900">{{ $notification->data['title'] ?? ($notification->data['member_name'] ?? 'Account update') }}</span><span class="mt-1 block text-xs leading-5 text-zinc-500">{{ $notification->data['body'] ?? (isset($notification->data['expires_at']) ? 'Membership expires '.$notification->data['expires_at'] : 'Open chat for details.') }}</span><time datetime="{{ $notification->created_at->format('c') }}" class="mt-1 block text-xs leading-5 text-zinc-500">{{ $notification->created_at->format('M j, Y · g:i A') }}</time><span class="mt-2 block text-xs font-bold text-lime-700">{{ isset($notification->data['sender_id']) ? 'Open conversation' : (($notification->data['route_name'] ?? null) === 'member.dashboard' ? 'View membership QR' : (isset($notification->data['route_name']) ? 'Open approval' : 'Open chat')) }}</span></span><svg class="size-4 shrink-0 text-zinc-400 transition group-hover:translate-x-0.5 group-hover:text-lime-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg></button></form>@empty<p class="rounded-xl bg-zinc-50 p-4 text-sm text-zinc-500">You’re all caught up.</p>@endforelse</div></div>
                    </details>
                    <details class="rush-menu relative"><summary class="flex min-h-11 cursor-pointer items-center rounded-lg px-1" aria-label="Open account menu"><span class="rush-avatar !size-9">{{ mb_strtoupper(mb_substr($portalUser->name, 0, 1)) }}</span></summary><div class="absolute right-0 top-full mt-2 w-56 rounded-xl border border-zinc-200 bg-white p-3 shadow-lg"><div class="flex items-start justify-between gap-2"><p class="break-words px-2 py-2 text-sm font-semibold">{{ $portalUser->name }}</p><button type="button" class="rounded-lg px-2 py-1 font-bold text-zinc-500 hover:bg-zinc-100 hover:text-zinc-950" data-menu-close aria-label="Close account menu">✕</button></div><a href="{{ route('profile.edit') }}" class="rush-secondary w-full">Change password</a></div></details>
                </div>
            </div>
        </header>
        <main class="mx-auto w-full max-w-[90rem] p-4 sm:p-6 lg:p-10">@if(session('success'))<div class="mb-6 rounded-xl border border-lime-200 bg-lime-50 px-4 py-3 text-sm font-medium text-lime-900">{{ session('success') }}</div>@endif @if(session('error'))<div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">{{ session('error') }}</div>@endif @yield('content')</main>
    </div>
</div>
<nav class="fixed inset-x-0 bottom-0 z-40 flex gap-1 overflow-x-auto border-t border-zinc-200 bg-white/95 px-2 pb-[max(.5rem,env(safe-area-inset-bottom))] pt-2 shadow-[0_-4px_20px_rgba(0,0,0,.06)] lg:hidden" aria-label="Mobile navigation">@foreach($navigation as [$label, $routeName, $active])@php($isActive = request()->routeIs(...(array) $active))<a href="{{ route($routeName) }}" class="flex min-h-14 min-w-20 flex-1 flex-col items-center justify-center rounded-xl px-2 text-[0.68rem] font-semibold {{ $isActive ? 'bg-lime-100 text-zinc-950' : 'text-zinc-600' }}"><span class="mb-1 size-1.5 rounded-full {{ $isActive ? 'bg-lime-500' : 'bg-zinc-300' }}"></span>{{ $label }}</a>@endforeach</nav>
</body>
</html>
