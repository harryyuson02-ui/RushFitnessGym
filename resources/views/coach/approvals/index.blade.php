@extends('layouts.portal')
@section('title', 'Member Approvals')
@section('content')
<x-page-heading eyebrow="Your review queue" title="Member approvals" description="Approve or decline membership applications from people who selected you as their coach." />

<section class="mt-7">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold">Pending applications</h2>
            <p class="mt-1 text-sm text-zinc-500">Review the selected plan and payment information before deciding.</p>
        </div>
        <span class="rush-badge bg-amber-100 text-amber-900">{{ $memberApplications->count() }} pending</span>
    </div>

    <div class="mt-4 grid gap-4">
        @forelse($memberApplications as $application)
            @php($transaction = $application->transactions->first())
            <article class="rush-card p-5 sm:p-6">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                    <div class="grid min-w-0 gap-4 sm:grid-cols-2 lg:grid-cols-4 lg:gap-8">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-zinc-400">Member</p>
                            <p class="mt-1 break-words font-bold">{{ $application->member->full_name }}</p>
                            <p class="text-sm text-zinc-500">{{ $application->member->member_code }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-zinc-400">Goal & availability</p>
                            <p class="mt-1 font-semibold">{{ $application->member->fitness_goal ?? 'Not specified' }}</p>
                            <p class="text-sm text-zinc-500">{{ implode(', ', $application->member->availability_days ?? []) ?: 'No days selected' }}</p>
                            @if($application->member->availability_starts_at && $application->member->availability_ends_at)
                                <p class="text-sm text-zinc-500">{{ date('g:i A', strtotime($application->member->availability_starts_at)) }}–{{ date('g:i A', strtotime($application->member->availability_ends_at)) }}</p>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-zinc-400">Plan</p>
                            <p class="mt-1 font-semibold">{{ $application->plan->name }}</p>
                            <p class="text-sm text-zinc-500">₱{{ number_format($application->plan->price, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-zinc-400">Payment</p>
                            <p class="mt-1 font-semibold uppercase">{{ $transaction?->payment_method ?? '—' }}</p>
                            <p class="break-words text-sm text-zinc-500">{{ $transaction?->reference ? 'Ref: '.$transaction->reference : 'No reference' }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('coach.approvals.update', $application) }}" class="grid shrink-0 gap-2 sm:grid-cols-2">
                        @csrf
                        @method('PATCH')
                        <button class="rush-secondary text-red-700" type="submit" name="decision" value="declined">Decline</button>
                        <button class="rush-primary" type="submit" name="decision" value="approved">Approve member</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="rush-card p-7 text-center">
                <p class="font-semibold">No applications are waiting</p>
                <p class="mt-2 text-sm text-zinc-500">New applications will appear here when a member selects you as their coach.</p>
            </div>
        @endforelse
    </div>
</section>
@endsection
