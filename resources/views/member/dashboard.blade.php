@extends('layouts.portal')
@section('title', 'Member Dashboard')
@section('content')
<x-page-heading eyebrow="Member portal" title="Welcome, {{ auth()->user()->name }}" description="Manage your membership, payments, and conversations." />
@if(!$member)
<section class="mt-7" aria-labelledby="application-title">
    <div class="mb-5"><p class="text-[0.65rem] font-bold uppercase tracking-[0.18em] text-lime-700">New member onboarding</p><h2 id="application-title" class="mt-2 text-2xl font-bold tracking-tight">Complete your membership application</h2><p class="mt-2 max-w-3xl text-sm leading-6 text-zinc-600">Tell us your goal and availability, then select your plan, coaching preference, and payment method.</p></div>
    @include('member.partials.application-form')
</section>
@else
<div class="mt-7 grid gap-4 sm:grid-cols-3"><div class="rush-stat"><span>Member ID</span><strong class="text-xl">{{ $member->member_code }}</strong></div><div class="rush-stat"><span>Plan</span><strong class="text-xl">{{ $member->currentSubscription?->plan?->name ?? 'Pending' }}</strong></div><div class="rush-stat"><span>Status</span><strong class="text-xl capitalize">{{ $member->currentSubscription?->status ?? 'Pending' }}</strong></div></div>
<div class="mt-6 grid gap-6 xl:grid-cols-2"><section class="rush-card p-5 sm:p-6"><h2 class="text-lg font-bold">Coach</h2><p class="mt-4 font-semibold">{{ $member->coach?->name ?? 'Not assigned' }}</p><p class="text-sm text-lime-800">{{ $member->coach?->specialty }}</p></section><section class="rush-card p-5 sm:p-6"><h2 class="text-lg font-bold">Upcoming sessions</h2><div class="mt-4 grid gap-3">@forelse($events as $event)<div class="rounded-xl bg-zinc-50 p-4"><p class="font-semibold">{{ $event->title }}</p><p class="mt-1 text-sm text-zinc-500">{{ $event->starts_at->format('M j, Y · g:i A') }} · {{ $event->coach?->name }}</p></div>@empty<p class="text-sm text-zinc-500">No sessions scheduled.</p>@endforelse</div></section></div>
@endif
@endsection
