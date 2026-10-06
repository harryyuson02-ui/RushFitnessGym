@extends('layouts.portal')
@section('title', 'Coaches')
@section('content')
@if ($credentials)
    <section class="rush-card mb-6 border border-lime-300 p-5" aria-labelledby="coach-credentials-title">
        <h2 id="coach-credentials-title" class="text-lg font-bold">Coach login credentials</h2>
        <p class="mt-2 text-sm text-zinc-600">Share these credentials securely with the coach. The temporary password is shown only once.</p>
        <dl class="mt-4 grid gap-3">
            <div><dt class="text-sm font-semibold">Login email</dt><dd class="break-all">{{ $credentials['email'] }}</dd></div>
            <div><dt class="text-sm font-semibold">Temporary password</dt><dd class="break-all font-mono">{{ $temporaryPassword }}</dd></div>
        </dl>
        <p class="mt-4 text-sm text-zinc-600">Sign in to open the coach dashboard, then choose Change password from the account menu.</p>
        <a href="{{ route('login') }}" class="mt-3 inline-block text-sm font-bold text-lime-700">Coach sign-in page</a>
    </section>
@endif
<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <x-page-heading eyebrow="Available roster" title="Coaches" description="Your coaching team and schedules." />
    <a href="{{ route('admin.coaches.create') }}" class="rush-primary">+ Add coach</a>
</div>
<form method="GET" action="{{ route('admin.coaches.index') }}" role="search" class="mt-5 grid gap-2 sm:grid-cols-[1fr_15rem_auto]">
    <label for="coach-search" class="sr-only">Search coaches by name or specialty</label>
    <input id="coach-search" class="rush-input" type="search" name="search" value="{{ $search }}" placeholder="Search coaches by name or specialty…" maxlength="100">
    <select class="rush-input" name="specialty" aria-label="Filter by specialty"><option value="">All specialties</option>@foreach(\App\Models\Coach::SPECIALTIES as $option)<option value="{{ $option }}" @selected($specialty === $option)>{{ $option }}</option>@endforeach</select>
    <button type="submit" class="rush-secondary" aria-label="Search coaches"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg></button>
</form>
<x-input-error :messages="$errors->all()" />
<div class="rush-card mt-4 overflow-hidden"><div class="overflow-x-auto"><table class="rush-table"><thead><tr><th>Coach</th><th>Specialty</th><th>Schedule</th><th>Clients</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($coaches as $coach)<tr><td><p class="font-semibold">{{ $coach->name }}</p><p class="text-xs text-zinc-500">{{ $coach->email ?? $coach->phone }}</p></td><td><span class="rush-badge bg-lime-50 text-lime-900">{{ $coach->specialty }}</span></td><td class="max-w-xs">{{ $coach->schedule }}</td><td>{{ $coach->members_count }}</td><td><span class="rush-badge {{ $coach->is_available ? 'bg-lime-100 text-lime-900' : 'bg-zinc-100 text-zinc-600' }}">{{ $coach->is_available ? 'Available' : 'Unavailable' }}</span></td><td><a href="{{ route('admin.coaches.edit', $coach) }}" class="rush-secondary">Edit</a></td></tr>@empty<tr><td colspan="6" class="py-10 text-center text-zinc-500">{{ $search ? 'No matching coaches' : 'No coaches yet' }}</td></tr>@endforelse
</tbody></table></div></div>
<div class="mt-6">{{ $coaches->links() }}</div>
@endsection
