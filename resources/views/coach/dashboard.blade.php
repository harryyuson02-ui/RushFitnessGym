@extends('layouts.portal')
@section('title', 'Coach Dashboard')
@section('content')
<x-page-heading eyebrow="Coach workspace" title="Welcome, {{ auth()->user()->name }}" description="Your clients, availability, and next sessions in one place." />
@if(!$coach)<div class="rush-card mt-6 border-amber-200 bg-amber-50 p-6"><p class="font-bold">Coach profile not linked</p><p class="mt-2 text-sm text-amber-800">Ask an administrator to link this login to a coach roster record.</p></div>@else
<div class="mt-7 grid gap-4 sm:grid-cols-3"><div class="rush-stat"><span>Assigned clients</span><strong>{{ $clientCount }}</strong></div><div class="rush-stat"><span>Roster status</span><strong class="text-xl">{{ $coach->is_available ? 'Available' : 'Unavailable' }}</strong></div><div class="rush-stat"><span>Specialty</span><strong class="text-xl">{{ $coach->specialty }}</strong></div></div>
<section class="rush-card mt-6 overflow-hidden" data-coach-calendar aria-labelledby="coach-calendar-title">
    <div class="flex flex-col gap-4 border-b border-zinc-200 p-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <div><p class="text-xs font-bold uppercase tracking-[0.16em] text-lime-700">Schedule calendar</p><h2 id="coach-calendar-title" class="mt-1 text-xl font-extrabold">{{ $calendarMonth->format('F Y') }}</h2><p class="mt-1 text-sm text-zinc-500">Select any date to open its schedule.</p></div>
        <div class="flex gap-2"><a href="{{ route('coach.dashboard', ['schedule_month' => $calendarMonth->subMonth()->format('Y-m')]) }}" class="rush-secondary !min-h-10 !px-3" aria-label="Previous month">←</a><a href="{{ route('coach.dashboard') }}" class="rush-secondary !min-h-10">Today</a><a href="{{ route('coach.dashboard', ['schedule_month' => $calendarMonth->addMonth()->format('Y-m')]) }}" class="rush-secondary !min-h-10 !px-3" aria-label="Next month">→</a></div>
    </div>
    <div class="overflow-x-auto"><div class="min-w-[42rem] p-4 sm:p-5">
        <div class="grid grid-cols-7 gap-1 text-center text-[0.65rem] font-bold uppercase tracking-wider text-zinc-400">@foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $weekday)<span class="py-2">{{ $weekday }}</span>@endforeach</div>
        <div class="grid grid-cols-7 gap-1.5">
            @foreach ($calendarDays as $day)
                <button type="button" class="group min-h-24 rounded-xl border p-2 text-left transition hover:-translate-y-0.5 hover:border-lime-400 hover:shadow-md {{ $day['isCurrentMonth'] ? 'border-zinc-200 bg-white' : 'border-zinc-100 bg-zinc-50 text-zinc-400' }} {{ $day['date']->isToday() ? 'ring-2 ring-lime-300' : '' }}" data-coach-calendar-date="{{ $day['date']->toDateString() }}" aria-haspopup="dialog" aria-label="Open schedule for {{ $day['date']->format('F j, Y') }}">
                    <span class="flex items-center justify-between gap-1"><time datetime="{{ $day['date']->toDateString() }}" class="text-sm font-extrabold">{{ $day['date']->day }}</time>@if($day['events']->isNotEmpty())<span class="flex min-w-5 items-center justify-center rounded-full bg-lime-400 px-1 text-[0.65rem] font-black text-zinc-950">{{ $day['events']->count() }}</span>@endif</span>
                    @if ($day['events']->isNotEmpty())<span class="mt-2 block truncate text-[0.7rem] font-semibold text-zinc-600">{{ $day['events']->first()->starts_at->format('g:i A') }} · {{ $day['events']->first()->title }}</span>@else<span class="mt-2 block text-[0.7rem] text-zinc-400">No sessions</span>@endif
                </button>
            @endforeach
        </div>
    </div></div>

    <dialog class="m-auto w-[min(36rem,calc(100vw-2rem))] rounded-2xl bg-white p-0 text-zinc-950 shadow-2xl backdrop:bg-zinc-950/70" data-coach-schedule-dialog>
        <div class="max-h-[calc(100dvh-2rem)] overflow-y-auto p-5 sm:p-6">
            <div class="flex items-start justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-[0.16em] text-lime-700">Daily schedule</p><h2 class="mt-1 text-2xl font-extrabold">Coach sessions</h2></div><button type="button" class="rush-secondary !size-11 !px-0" data-coach-schedule-close aria-label="Close daily schedule">✕</button></div>
            <div class="mt-5">
                @foreach ($calendarDays as $day)
                    <section data-coach-schedule-panel="{{ $day['date']->toDateString() }}" hidden>
                        <h3 class="text-lg font-extrabold">{{ $day['date']->format('l, F j, Y') }}</h3><p class="mt-1 text-sm text-zinc-500">{{ $day['events']->count() }} {{ Str::plural('session', $day['events']->count()) }}</p>
                        <div class="mt-4 grid gap-3">
                            @forelse ($day['events'] as $event)
                                <article class="rounded-xl border border-zinc-200 bg-zinc-50 p-4"><div class="flex items-start justify-between gap-4"><div><h4 class="font-extrabold">{{ $event->title }}</h4><p class="mt-1 text-sm text-zinc-600">{{ $event->member?->full_name ?? 'No client assigned' }}</p></div><time datetime="{{ $event->starts_at->format('c') }}" class="shrink-0 text-sm font-bold text-lime-800">{{ $event->starts_at->format('g:i A') }}</time></div>@if ($event->notes)<p class="mt-3 border-t border-zinc-200 pt-3 text-sm leading-6 text-zinc-500">{{ $event->notes }}</p>@endif</article>
                            @empty
                                <p class="rounded-xl bg-zinc-50 p-6 text-center text-sm text-zinc-500">No sessions scheduled for this date.</p>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </dialog>
</section>
<section class="rush-card mt-6 p-5 sm:p-6"><h2 class="text-xl font-bold">Upcoming schedule</h2><div class="mt-4 grid gap-3">@forelse($upcomingEvents as $event)<div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-zinc-50 p-4"><div><p class="font-semibold">{{ $event->title }}</p><p class="text-sm text-zinc-500">{{ $event->member?->full_name ?? 'No client assigned' }}</p></div><time class="text-sm font-semibold text-lime-800">{{ $event->starts_at->format('M j · g:i A') }}</time></div>@empty<p class="rounded-xl bg-zinc-50 p-5 text-sm text-zinc-500">No upcoming sessions.</p>@endforelse</div></section>
@endif
@endsection
