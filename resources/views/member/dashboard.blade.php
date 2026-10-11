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
@if($activeSubscription)
<section class="rush-card mt-6 overflow-hidden" aria-labelledby="membership-qr-title">
    <div class="grid gap-6 p-5 sm:grid-cols-[minmax(0,18rem)_1fr] sm:items-center sm:p-7">
        <a href="{{ route('access-key.image', ['token' => $activeSubscription->access_token, 'download' => 1]) }}" download="{{ $member->member_code }}-access-qr.svg" class="group mx-auto block rounded-2xl border border-zinc-200 bg-white p-3 shadow-sm transition hover:-translate-y-0.5 hover:border-lime-400 hover:shadow-md" aria-label="Download QR access key for {{ $member->full_name }}">
            <img class="w-full max-w-64" src="{{ route('access-key.image', $activeSubscription->access_token) }}" alt="QR access key for {{ $member->full_name }}">
        </a>
        <div>
            <p class="text-[0.65rem] font-bold uppercase tracking-[0.18em] text-lime-700">Active gym access</p>
            <h2 id="membership-qr-title" class="mt-2 text-2xl font-extrabold">Your membership QR</h2>
            <p class="mt-2 font-semibold">{{ $member->full_name }}</p>
            <p class="mt-1 text-sm text-zinc-500">{{ $activeSubscription->plan->name }} · Valid through {{ $activeSubscription->expires_at->format('M j, Y') }}</p>
            <p class="mt-4 text-sm leading-6 text-zinc-600">Present this QR at the gym entrance. Click the QR or use the button below to save a personalized copy.</p>
            <a href="{{ route('access-key.image', ['token' => $activeSubscription->access_token, 'download' => 1]) }}" download="{{ $member->member_code }}-access-qr.svg" class="rush-primary mt-5">Download membership QR</a>
        </div>
    </div>
</section>
@endif
<div class="mt-6 grid gap-6 xl:grid-cols-2"><section class="rush-card p-5 sm:p-6"><h2 class="text-lg font-bold">Coach</h2><p class="mt-4 font-semibold">{{ $member->coach?->name ?? 'Not assigned' }}</p><p class="text-sm text-lime-800">{{ $member->coach?->specialty }}</p></section><section class="rush-card p-5 sm:p-6"><h2 class="text-lg font-bold">Upcoming sessions</h2><div class="mt-4 grid gap-3">@forelse($events as $event)<div class="rounded-xl bg-zinc-50 p-4"><p class="font-semibold">{{ $event->title }}</p><p class="mt-1 text-sm text-zinc-500">{{ $event->starts_at->format('M j, Y · g:i A') }} · {{ $event->coach?->name }}</p></div>@empty<p class="text-sm text-zinc-500">No sessions scheduled.</p>@endforelse</div></section></div>
@if($member->pendingCoachAssignment)
<section class="rush-card mt-6 p-5 sm:p-6">
    <h2 class="text-lg font-bold">Training request pending</h2>
    <p class="mt-2 text-sm text-zinc-600">{{ $member->pendingCoachAssignment->coach->name }} is reviewing your request. Your gym membership remains active while you wait.</p>
</section>
@elseif($member->coach_id === null && $availableCoaches->isNotEmpty())
<section class="rush-card mt-6 p-5 sm:p-6" aria-labelledby="coach-request-title">
    <h2 id="coach-request-title" class="text-lg font-bold">Choose a coach</h2>
    <p class="mt-2 text-sm text-zinc-600">Your membership is active. Select an available coach to send a training request.</p>
    <form method="POST" action="{{ route('member.coach-request.store') }}" class="mt-4 flex flex-col gap-3 sm:flex-row">
        @csrf
        <label for="coach_id" class="sr-only">Available coach</label>
        <select id="coach_id" name="coach_id" class="rush-input" required>
            <option value="">Select a coach</option>
            @foreach($availableCoaches as $availableCoach)
                <option value="{{ $availableCoach->id }}">{{ $availableCoach->name }} — {{ $availableCoach->specialty }}</option>
            @endforeach
        </select>
        <button type="submit" class="rush-primary shrink-0">Send training request</button>
    </form>
    <x-input-error :messages="$errors->get('coach_id')" />
</section>
@endif
@if($canApply)
<section class="mt-8" aria-labelledby="reapplication-title">
    <div class="mb-5"><p class="text-[0.65rem] font-bold uppercase tracking-[0.18em] text-lime-700">Membership access</p><h2 id="reapplication-title" class="mt-2 text-2xl font-bold tracking-tight">Submit another membership application</h2><p class="mt-2 max-w-3xl text-sm leading-6 text-zinc-600">Choose a new plan and update your coaching preferences.</p></div>
    @include('member.partials.application-form')
</section>
@endif
@endif
@endsection
