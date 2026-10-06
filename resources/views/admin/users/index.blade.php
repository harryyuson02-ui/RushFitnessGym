@extends('layouts.portal')
@section('title', 'Users')
@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><x-page-heading eyebrow="Account directory" title="Users" description="All administrator, coach, and member login accounts." /><a href="{{ route('admin.users.create') }}" class="rush-primary">+ Add account</a></div>
<form method="GET" class="mt-6 grid gap-3 sm:grid-cols-[1fr_12rem_auto]">
    <input class="rush-input" name="search" value="{{ $search }}" placeholder="Search name or email">
    <select class="rush-input" name="role"><option value="all">All roles</option>@foreach(['admin' => 'Admins', 'coach' => 'Coaches', 'user' => 'Users'] as $value => $label)<option value="{{ $value }}" @selected($role === $value)>{{ $label }}</option>@endforeach</select>
    <button class="rush-primary" type="submit">Filter</button>
</form>
<div class="rush-card mt-5 overflow-hidden"><div class="overflow-x-auto"><table class="rush-table"><thead><tr><th>User</th><th>Role</th><th>Linked record</th><th>Contact</th><th></th></tr></thead><tbody>
@forelse($users as $user)<tr><td><p class="font-semibold">{{ $user->name }}</p><p class="text-xs text-zinc-500">{{ $user->email }}</p></td><td><span class="rush-badge bg-zinc-100 capitalize">{{ $user->role }}</span></td><td>{{ $user->coach?->specialty ?? $user->member?->member_code ?? '—' }}</td><td>{{ $user->phone ?? 'Not added' }}</td><td><a class="rush-secondary" href="{{ route('admin.users.edit', $user) }}">Edit</a></td></tr>@empty<tr><td colspan="5" class="py-10 text-center text-zinc-500">No matching users.</td></tr>@endforelse
</tbody></table></div></div><div class="mt-5">{{ $users->links() }}</div>
@endsection
