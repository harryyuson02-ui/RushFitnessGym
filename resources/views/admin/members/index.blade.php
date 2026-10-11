@extends('layouts.portal')
@section('title', 'Members')
@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div class="flex items-start justify-between gap-3">
        <x-page-heading eyebrow="Client records" title="Members" description="Manage member profiles and access." />
        <span class="rush-badge mt-7 bg-zinc-200 text-zinc-600" data-member-total>{{ $totalMembers }} total</span>
    </div>
</div>
<form method="GET" action="{{ route('admin.members.index') }}" role="search" class="mt-5 flex gap-2">
    <label for="member-search" class="sr-only">Search members by name or ID</label>
    <input id="member-search" class="rush-input" type="search" name="member_search" value="{{ $memberSearch }}" placeholder="Search members by name or ID…" maxlength="100">
    <input type="hidden" name="status" value="{{ $status }}">
    <input type="hidden" name="user_search" value="{{ $userSearch }}">
    <input type="hidden" name="user_role" value="{{ $userRole }}">
    <button type="submit" class="rush-secondary" aria-label="Search members"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg></button>
</form>
<x-input-error :messages="$errors->all()" />
<nav class="my-4 flex flex-wrap gap-2" aria-label="Member status filters">
    @foreach (['all' => 'All', 'pending' => 'Pending', 'active' => 'Active', 'expiring' => 'Expiring', 'inactive' => 'Inactive'] as $value => $label)
        <a href="{{ route('admin.members.index', ['member_search' => $memberSearch, 'status' => $value, 'user_search' => $userSearch, 'user_role' => $userRole]) }}" @if($status === $value) aria-current="page" @endif class="inline-flex min-h-11 items-center rounded-full px-4 text-xs font-semibold {{ $status === $value ? 'bg-lime-200 text-lime-950' : 'bg-zinc-200/70 text-zinc-700 hover:bg-zinc-200' }}">{{ $label }}</a>
    @endforeach
</nav>
<div data-member-list><div class="rush-card overflow-hidden"><div class="max-h-[28rem] overflow-auto overscroll-contain" tabindex="0" aria-label="Scrollable members list"><table class="rush-table"><thead class="sticky top-0 z-10"><tr><th>Member</th><th>Coach</th><th>Plan</th><th>Expires</th><th>Status</th></tr></thead><tbody>
    @forelse ($members as $member)
        @php
            $subscription = $member->currentSubscription;
            $isPending = $subscription?->status === 'pending';
            $isActive = $subscription && $subscription->status === 'active' && $subscription->expires_at->gte(today());
            $isExpiring = $isActive && $subscription->expires_at->lte(today()->addDays(3));
            $label = $isPending ? 'Pending' : ($isExpiring ? 'Expiring' : ($isActive ? 'Active' : 'Inactive'));
        @endphp
        <tr><td><p class="font-semibold">{{ $member->full_name }}</p><p class="text-xs text-zinc-500">{{ $member->member_code }}</p></td><td>{{ $member->coach?->name ?? 'Unassigned' }}</td><td>{{ $subscription?->plan?->name ?? 'No plan' }}</td><td>{{ $subscription?->expires_at->format('M j, Y') ?? '—' }}</td><td><span class="rush-badge {{ $isPending ? 'bg-sky-100 text-sky-900' : ($isExpiring ? 'bg-amber-100 text-amber-900' : ($isActive ? 'bg-lime-100 text-lime-900' : 'bg-zinc-100 text-zinc-600')) }}">{{ $label }}</span></td></tr>
    @empty
        <tr><td colspan="5" class="py-10 text-center text-zinc-500">{{ $totalMembers ? 'No matching members' : 'No members yet' }}</td></tr>
    @endforelse
</tbody></table></div></div>
<div class="mt-6">{{ $members->links() }}</div></div>

<section id="system-users" class="mt-12 scroll-mt-6" aria-labelledby="system-users-heading">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-[0.65rem] font-bold uppercase tracking-[0.18em] text-lime-700">Account directory</p>
            <h2 id="system-users-heading" class="mt-1 text-2xl font-extrabold tracking-tight">System users</h2>
            <p class="mt-1 text-sm text-zinc-500">Administrator, coach, and member login accounts.</p>
        </div>
        <span class="rush-badge bg-zinc-200 text-zinc-600">{{ $totalUsers }} total</span>
    </div>

    <form method="GET" action="{{ route('admin.members.index').'#system-users' }}" role="search" class="mt-5 grid gap-3 sm:grid-cols-[1fr_12rem_auto]">
        <label for="user-search" class="sr-only">Search system users by name or email</label>
        <input id="user-search" class="rush-input" type="search" name="user_search" value="{{ $userSearch }}" placeholder="Search system users by name or email…" maxlength="100">
        <label for="user-role" class="sr-only">Filter system users by role</label>
        <select id="user-role" class="rush-input" name="user_role"><option value="all">All roles</option>@foreach(['admin' => 'Admins', 'coach' => 'Coaches', 'user' => 'Members'] as $value => $label)<option value="{{ $value }}" @selected($userRole === $value)>{{ $label }}</option>@endforeach</select>
        <input type="hidden" name="member_search" value="{{ $memberSearch }}">
        <input type="hidden" name="status" value="{{ $status }}">
        <button class="rush-primary" type="submit">Search users</button>
    </form>

    <div class="rush-card mt-5 overflow-hidden">
        <div class="max-h-[32rem] overflow-auto overscroll-contain" tabindex="0" aria-label="Scrollable system users list">
            <table class="rush-table">
                <thead class="sticky top-0 z-10 bg-white"><tr><th>User</th><th>Role</th><th>Linked record</th><th>Contact</th><th></th></tr></thead>
                <tbody>
                @forelse($users as $user)
                    <tr><td><p class="font-semibold">{{ $user->name }}</p><p class="text-xs text-zinc-500">{{ $user->email }}</p></td><td><span class="rush-badge bg-zinc-100 capitalize">{{ $user->role }}</span></td><td>{{ $user->coach?->specialty ?? $user->member?->member_code ?? '—' }}</td><td>{{ $user->phone ?? 'Not added' }}</td><td><a class="rush-secondary" href="{{ route('admin.users.edit', $user) }}">Edit</a></td></tr>
                @empty
                    <tr><td colspan="5" class="py-10 text-center text-zinc-500">No matching system users.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
