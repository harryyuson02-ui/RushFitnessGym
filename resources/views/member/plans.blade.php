@extends('layouts.portal')
@section('title', 'Membership Plans')
@section('content')
<x-page-heading eyebrow="Membership options" title="Membership plans" description="Choose the access period that best fits your training routine." />

@if ($currentSubscription)
    <section class="rush-card mt-6 flex flex-col gap-4 border-lime-200 bg-lime-50/70 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6" aria-labelledby="current-membership-title">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-lime-800">Your membership</p>
            <h2 id="current-membership-title" class="mt-1 text-xl font-bold text-zinc-950">{{ $currentSubscription->plan?->name ?? 'Plan unavailable' }}</h2>
            <p class="mt-1 text-sm text-zinc-600">
                {{ $currentSubscription->starts_at->format('M j, Y') }} – {{ $currentSubscription->expires_at->format('M j, Y') }}
            </p>
        </div>
        <span class="rush-badge bg-white capitalize text-zinc-700 ring-1 ring-lime-200">{{ $currentSubscription->status }}</span>
    </section>
@endif

<section class="mt-7" aria-labelledby="available-plans-title">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 id="available-plans-title" class="text-xl font-bold text-zinc-950">Available plans</h2>
            <p class="mt-1 text-sm text-zinc-500">All prices are shown in Philippine pesos.</p>
        </div>
        @if (auth()->user()->member)
            <a href="{{ route('chat.index') }}" class="text-sm font-bold text-lime-700 hover:text-lime-800">Ask about changing plans →</a>
        @endif
    </div>

    <div class="mt-5 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($plans as $plan)
            @php($isCurrentPlan = $currentSubscription?->membership_plan_id === $plan->id)
            <article class="rush-card relative flex min-h-72 flex-col overflow-hidden p-6 {{ $isCurrentPlan ? 'border-lime-400 ring-2 ring-lime-100' : '' }}">
                @if ($isCurrentPlan)
                    <span class="rush-badge absolute right-5 top-5 bg-lime-100 text-lime-900">Current plan</span>
                @endif
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-zinc-500">{{ $plan->duration_days }} days</p>
                <h3 class="mt-3 pr-24 text-2xl font-bold tracking-tight text-zinc-950">{{ $plan->name }}</h3>
                <p class="mt-4 text-3xl font-black tracking-tight text-zinc-950">₱{{ number_format($plan->price, 2) }}</p>
                <p class="mt-4 flex-1 text-sm leading-6 text-zinc-600">{{ $plan->description }}</p>
                <div class="mt-6 border-t border-zinc-100 pt-4">
                    <p class="text-sm font-semibold text-zinc-700">Gym access for the full {{ $plan->duration_days }}-day period</p>
                </div>
            </article>
        @empty
            <div class="rush-card p-8 text-center md:col-span-2 xl:col-span-3">
                <p class="font-bold text-zinc-900">No plans are available right now.</p>
                <p class="mt-1 text-sm text-zinc-500">Please check back later or message the gym for assistance.</p>
            </div>
        @endforelse
    </div>
</section>

@if (! auth()->user()->member)
    <section class="mt-6 rounded-2xl bg-zinc-950 p-6 text-white sm:flex sm:items-center sm:justify-between sm:gap-6">
        <div>
            <h2 class="text-xl font-bold">Ready to join?</h2>
            <p class="mt-1 text-sm leading-6 text-zinc-400">Choose your plan while completing the membership application.</p>
        </div>
        <a href="{{ route('member.application.create') }}" class="rush-primary mt-5 w-full sm:mt-0 sm:w-auto">Start application</a>
    </section>
@endif
@endsection
