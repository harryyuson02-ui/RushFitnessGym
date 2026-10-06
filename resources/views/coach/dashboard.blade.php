@extends('layouts.portal')
@section('title', 'Coach Dashboard')
@section('content')
<x-page-heading eyebrow="Coach workspace" title="Welcome, {{ auth()->user()->name }}" description="Your clients, availability, and next sessions in one place." />
@if(!$coach)<div class="rush-card mt-6 border-amber-200 bg-amber-50 p-6"><p class="font-bold">Coach profile not linked</p><p class="mt-2 text-sm text-amber-800">Ask an administrator to link this login to a coach roster record.</p></div>@else
<div class="mt-7 grid gap-4 sm:grid-cols-3"><div class="rush-stat"><span>Assigned clients</span><strong>{{ $clientCount }}</strong></div><div class="rush-stat"><span>Roster status</span><strong class="text-xl">{{ $coach->is_available ? 'Available' : 'Unavailable' }}</strong></div><div class="rush-stat"><span>Specialty</span><strong class="text-xl">{{ $coach->specialty }}</strong></div></div>
<section class="rush-card mt-6 p-5 sm:p-6"><h2 class="text-xl font-bold">Upcoming schedule</h2><div class="mt-4 grid gap-3">@forelse($upcomingEvents as $event)<div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-zinc-50 p-4"><div><p class="font-semibold">{{ $event->title }}</p><p class="text-sm text-zinc-500">{{ $event->member?->full_name ?? 'No client assigned' }}</p></div><time class="text-sm font-semibold text-lime-800">{{ $event->starts_at->format('M j · g:i A') }}</time></div>@empty<p class="rounded-xl bg-zinc-50 p-5 text-sm text-zinc-500">No upcoming sessions.</p>@endforelse</div></section>
@endif
@endsection
