@extends('layouts.admin')

@section('title', 'Members')

@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <x-page-heading eyebrow="Client records" title="Members" description="Profiles, coaches, plans, and access status." />
    <a href="{{ route('admin.members.create') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-zinc-950 px-5 font-bold text-white">+ Onboard member</a>
</div>

<div class="mt-7 grid gap-3">
    @forelse ($members as $member)
        @php($subscription = $member->currentSubscription)
        <a href="{{ route('admin.members.show', $member) }}" class="grid gap-4 rounded-xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:border-lime-400 hover:bg-zinc-50 sm:grid-cols-[1fr_auto] sm:items-center">
            <div class="flex items-start gap-4">
                <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-lime-100 font-extrabold text-lime-800">{{ substr($member->first_name, 0, 1) }}{{ substr($member->last_name, 0, 1) }}</div>
                <div><h2 class="font-extrabold">{{ $member->full_name }}</h2><p class="mt-1 text-sm text-zinc-500">{{ $member->member_code }} · {{ $member->phone }}</p><p class="mt-1 text-sm text-zinc-500">Coach: {{ $member->coach?->name ?? 'Unassigned' }}</p></div>
            </div>
            <div class="flex items-center justify-between gap-4 sm:block sm:text-right">
                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $subscription?->status === 'active' && $subscription->expires_at->isFuture() ? 'bg-lime-100 text-lime-800' : 'bg-red-100 text-red-700' }}">{{ ucfirst($subscription?->status ?? 'No plan') }}</span>
                <p class="mt-2 text-sm text-zinc-500">{{ $subscription?->plan?->name ?? '—' }} @if($subscription) · ends {{ $subscription->expires_at->format('M j') }} @endif</p>
            </div>
        </a>
    @empty
        <div class="rounded-xl border border-dashed border-zinc-300 bg-white p-10 text-center"><p class="font-bold">No members yet</p><p class="mt-2 text-sm text-zinc-500">Start with an in-person enrollment.</p></div>
    @endforelse
</div>
@endsection
