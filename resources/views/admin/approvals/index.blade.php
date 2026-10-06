@extends('layouts.portal')
@section('title', 'Approval Reviews')
@section('content')
<x-page-heading eyebrow="Member and coach operations" title="Approval reviews" description="Review gym-only membership applications and coach availability changes. Applications with a coach are reviewed by their selected coach." />

<div class="mt-7 grid gap-7">
    <section>
        <div class="flex items-center justify-between gap-4"><div><h2 class="text-xl font-bold">Gym-only membership applications</h2><p class="mt-1 text-sm text-zinc-500">Review members requesting independent gym access without a coach.</p></div><span class="rush-badge bg-amber-100 text-amber-900">{{ $memberApplications->count() }} pending</span></div>
        <div class="mt-4 grid gap-4">
            @forelse($memberApplications as $application)
                @php($transaction = $application->transactions->first())
                <article class="rush-card p-5 sm:p-6">
                    <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                        <div class="grid min-w-0 gap-4 sm:grid-cols-3 lg:gap-8">
                            <div><p class="text-xs font-bold uppercase tracking-wider text-zinc-400">Member</p><p class="mt-1 break-words font-bold">{{ $application->member->full_name }}</p><p class="text-sm text-zinc-500">{{ $application->member->member_code }}</p></div>
                            <div><p class="text-xs font-bold uppercase tracking-wider text-zinc-400">Plan</p><p class="mt-1 font-semibold">{{ $application->plan->name }}</p><p class="text-sm text-zinc-500">₱{{ number_format($application->plan->price, 2) }}</p></div>
                            <div><p class="text-xs font-bold uppercase tracking-wider text-zinc-400">Payment</p><p class="mt-1 font-semibold uppercase">{{ $transaction?->payment_method ?? '—' }}</p><p class="break-words text-sm text-zinc-500">{{ $transaction?->reference ? 'Ref: '.$transaction->reference : 'No reference' }}</p></div>
                        </div>
                        <form method="POST" action="{{ route('admin.approvals.members.update', $application) }}" class="grid shrink-0 gap-2 sm:grid-cols-2">@csrf @method('PATCH')<button class="rush-secondary text-red-700" type="submit" name="decision" value="declined">Decline</button><button class="rush-primary" type="submit" name="decision" value="approved">Approve member</button></form>
                    </div>
                </article>
            @empty
                <div class="rush-card p-7 text-center text-sm text-zinc-500">No gym-only membership applications are waiting for review.</div>
            @endforelse
        </div>
    </section>

    <section>
        <div class="flex items-center justify-between gap-4"><div><h2 class="text-xl font-bold">Coach schedule updates</h2><p class="mt-1 text-sm text-zinc-500">Approved changes replace the coach’s current weekly availability.</p></div><span class="rush-badge bg-amber-100 text-amber-900">{{ $availabilityChanges->count() }} pending</span></div>
        <form method="GET" action="{{ route('admin.approvals.index') }}" role="search" class="mt-4 flex gap-2">
            <label for="coach-first-name-search" class="sr-only">Search schedule updates by coach first name</label>
            <input id="coach-first-name-search" class="rush-input" type="search" name="search" value="{{ $search }}" placeholder="Search by coach first name…" maxlength="100">
            <button type="submit" class="rush-secondary" aria-label="Search schedule updates"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg></button>
        </form>
        <x-input-error :messages="$errors->all()" />
        <div class="mt-4 grid gap-4">
            @forelse($availabilityChanges as $change)
                <article class="rush-card p-5 sm:p-6">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0"><p class="text-lg font-bold">{{ $change->coach->name }}</p><p class="text-sm text-zinc-500">{{ $change->coach->specialty }} · Submitted {{ $change->created_at->diffForHumans() }}</p><p class="mt-2 text-sm"><span class="font-semibold">Current:</span> {{ $change->coach->schedule }}</p><div class="mt-3 flex flex-wrap gap-2">@forelse($change->slots as $slot)<span class="rush-badge bg-lime-50 text-lime-900">{{ ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][$slot['day_of_week']] }} {{ date('g:i A', strtotime($slot['starts_at'])) }}–{{ date('g:i A', strtotime($slot['ends_at'])) }}</span>@empty<span class="rush-badge bg-zinc-100 text-zinc-600">No available days</span>@endforelse</div></div>
                        <form method="POST" action="{{ route('admin.approvals.availability.update', $change) }}" class="flex shrink-0 gap-2">@csrf @method('PATCH')<button class="rush-secondary text-red-700" type="submit" name="decision" value="declined">Decline</button><button class="rush-primary" type="submit" name="decision" value="approved">Approve schedule</button></form>
                    </div>
                </article>
            @empty
                <div class="rush-card p-7 text-center text-sm text-zinc-500">{{ $search !== '' ? 'No matching coach schedule updates.' : 'No coach schedule updates are waiting for review.' }}</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
