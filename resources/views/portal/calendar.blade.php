@extends('layouts.portal')
@section('title', 'Calendar')
@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <x-page-heading eyebrow="Client sessions" title="{{ $monthStart->format('F Y') }}" description="A monthly view of scheduled sessions with your clients." />
    <div class="flex gap-2"><a href="{{ route('calendar.index', ['month' => $previousMonth]) }}" class="rush-secondary" aria-label="Previous month">← Previous</a><a href="{{ route('calendar.index', ['month' => now()->format('Y-m')]) }}" class="rush-secondary">Today</a><a href="{{ route('calendar.index', ['month' => $nextMonth]) }}" class="rush-secondary" aria-label="Next month">Next →</a></div>
</div>
<section class="rush-card mt-6 overflow-hidden">
    <div class="overflow-x-auto">
        <div class="min-w-[64rem]">
            <div class="grid grid-cols-7 border-b border-zinc-200 bg-zinc-50">@foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $dayName)<div class="px-3 py-3 text-center text-xs font-bold uppercase tracking-wider text-zinc-500">{{ $dayName }}</div>@endforeach</div>
            <div class="grid grid-cols-7">
                @foreach($calendarDays as $day)
                    <article class="min-h-40 border-b border-r border-zinc-200 p-2 {{ $day['is_current_month'] ? 'bg-white' : 'bg-zinc-50 text-zinc-400' }}">
                        <div class="flex justify-end"><time datetime="{{ $day['date']->toDateString() }}" class="flex size-8 items-center justify-center rounded-full text-sm font-bold {{ $day['date']->isToday() ? 'bg-lime-400 text-zinc-950' : '' }}">{{ $day['date']->day }}</time></div>
                        <div class="mt-1 grid gap-1.5">
                            @foreach($day['events'] as $event)
                                <div class="rounded-lg border-l-4 border-lime-500 bg-lime-50 p-2 text-zinc-900" title="{{ $event->title }}">
                                    <p class="truncate text-xs font-bold">{{ $event->starts_at->format('g:i A') }} · {{ $event->title }}</p>
                                    <p class="mt-0.5 truncate text-[0.68rem] text-zinc-600">{{ auth()->user()->isCoach() ? $event->member?->full_name : $event->coach?->name }}</p>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endsection
