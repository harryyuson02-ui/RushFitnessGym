@extends('layouts.portal')
@section('title', 'Training Requests')
@section('content')
<x-page-heading eyebrow="Your review queue" title="Training requests" description="Accept or decline active gym members who requested you as their coach." />

<section class="mt-7">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold">Pending requests</h2>
            <p class="mt-1 text-sm text-zinc-500">Review each member’s goal and availability before deciding.</p>
        </div>
        <span class="rush-badge bg-amber-100 text-amber-900">{{ $assignmentRequests->count() }} pending</span>
    </div>

    <div class="mt-4 grid gap-4">
        @forelse($assignmentRequests as $assignment)
            <article class="rush-card p-5 sm:p-6">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                    <div class="grid min-w-0 gap-4 sm:grid-cols-3 lg:gap-8">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-zinc-400">Member</p>
                            <p class="mt-1 break-words font-bold">{{ $assignment->member->full_name }}</p>
                            <p class="text-sm text-zinc-500">{{ $assignment->member->member_code }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-zinc-400">Goal & availability</p>
                            <p class="mt-1 font-semibold">{{ $assignment->member->fitness_goal ?? 'Not specified' }}</p>
                            <p class="text-sm text-zinc-500">{{ implode(', ', $assignment->member->availability_days ?? []) ?: 'No days selected' }}</p>
                            @if($assignment->member->availability_starts_at && $assignment->member->availability_ends_at)
                                <p class="text-sm text-zinc-500">{{ date('g:i A', strtotime($assignment->member->availability_starts_at)) }}–{{ date('g:i A', strtotime($assignment->member->availability_ends_at)) }}</p>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-zinc-400">Requested</p>
                            <p class="mt-1 font-semibold">{{ $assignment->created_at->format('M j, Y') }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('coach.approvals.update', $assignment) }}" class="grid shrink-0 gap-2 sm:grid-cols-2">
                        @csrf
                        @method('PATCH')
                        <button class="rush-secondary text-red-700" type="submit" name="decision" value="declined">Decline request</button>
                        <button class="rush-primary" type="submit" name="decision" value="accepted">Accept client</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="rush-card p-7 text-center">
                <p class="font-semibold">No training requests are waiting</p>
                <p class="mt-2 text-sm text-zinc-500">Requests appear here after an administrator activates the member’s gym membership.</p>
            </div>
        @endforelse
    </div>
</section>
@endsection
