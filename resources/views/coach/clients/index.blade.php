@extends('layouts.portal')
@section('title', 'My Clients')
@section('content')
<x-page-heading eyebrow="Own roster" title="My clients" description="Only members you approved with an active membership appear here." />
<div class="rush-card mt-6 overflow-hidden"><div class="overflow-x-auto"><table class="rush-table"><thead><tr><th>Client</th><th>Contact</th><th>Membership</th><th>Expires</th><th></th></tr></thead><tbody>@forelse($clients as $client)<tr><td><p class="font-semibold">{{ $client->full_name }}</p><p class="text-xs text-zinc-500">{{ $client->member_code }}</p></td><td>{{ $client->phone }}<p class="text-xs text-zinc-500">{{ $client->preferred_channel === 'sms' ? 'SMS' : 'Private message' }}</p></td><td>{{ $client->currentSubscription?->plan?->name ?? 'No plan' }}</td><td>{{ $client->currentSubscription?->expires_at?->format('M j, Y') ?? '—' }}</td><td><a class="rush-secondary" href="{{ route('coach.clients.show', $client) }}">Open record</a></td></tr>@empty<tr><td colspan="5" class="py-10 text-center text-zinc-500">No clients assigned.</td></tr>@endforelse</tbody></table></div></div><div class="mt-5">{{ $clients->links() }}</div>
@endsection
