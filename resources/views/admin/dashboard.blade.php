@extends('layouts.portal')

@section('title', 'Admin Dashboard')

@section('content')

<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-[0.7rem] font-bold uppercase tracking-[0.16em] text-lime-700">Operations overview</p>
        <h1 class="mt-1.5 text-3xl font-bold tracking-tight text-zinc-950 sm:text-4xl">Good day, Admin</h1>
        <p class="mt-2 text-sm leading-6 text-zinc-500 sm:text-base">Everything requiring attention at the front desk.</p>
    </div>
</div>

<div class="mt-8 grid gap-4 sm:grid-cols-3">
    <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm"><div class="flex items-center justify-between"><p class="text-sm font-medium text-zinc-500">Total members</p><span class="size-2 rounded-full bg-lime-500"></span></div><p class="mt-3 text-3xl font-bold tracking-tight text-zinc-950">{{ $memberCount }}</p></div>
    <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm"><div class="flex items-center justify-between"><p class="text-sm font-medium text-zinc-500">Active subscriptions</p><span class="size-2 rounded-full bg-lime-500"></span></div><p class="mt-3 text-3xl font-bold tracking-tight text-zinc-950">{{ $activeSubscriptionCount }}</p></div>
    <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm"><div class="flex items-center justify-between"><p class="text-sm font-medium text-zinc-500">Available coaches</p><span class="size-2 rounded-full bg-lime-500"></span></div><p class="mt-3 text-3xl font-bold tracking-tight text-zinc-950">{{ $availableCoachCount }}</p></div>
</div>

<div class="mt-7 grid gap-6">
    <section class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex items-center justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-600">Next 3 days</p><h2 class="mt-1 text-xl font-extrabold">Expiring subscriptions</h2></div><span class="rounded-full bg-amber-100 px-3 py-1 text-sm font-bold text-amber-800">{{ $expiringSubscriptions->count() }}</span></div>
        <div class="mt-5 grid gap-3">
            @forelse ($expiringSubscriptions as $subscription)
                <div class="flex items-center justify-between gap-4 rounded-lg border border-zinc-200 p-4">
                    <div><p class="font-bold">{{ $subscription->member->full_name }}</p><p class="mt-1 text-sm text-zinc-500">{{ $subscription->plan->name }} · {{ $subscription->member->coach?->name ?? 'No coach' }}</p></div>
                    <div class="text-right"><p class="text-sm font-bold text-amber-700">{{ $subscription->expires_at->diffForHumans() }}</p><p class="text-xs text-zinc-400">{{ $subscription->expires_at->format('M j, Y') }}</p></div>
                </div>
            @empty
                <div class="rounded-xl bg-zinc-50 p-6 text-center text-sm text-zinc-500">No subscriptions expire in the next three days.</div>
            @endforelse
        </div>
    </section>

</div>

@endsection
