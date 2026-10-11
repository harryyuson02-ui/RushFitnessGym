@extends('layouts.portal')
@section('title', 'My Coach Records')
@section('content')
<x-page-heading eyebrow="Training history" title="My coach records" description="Read progress notes and assessments recorded by your coaches." />

<div class="mt-6 grid gap-4">
    @forelse($records ?? [] as $record)
        <article class="rush-card p-5 sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-lime-700">{{ $record->coach?->name ?? 'Former coach' }}</p>
                    <h2 class="mt-2 text-xl font-bold text-zinc-950">{{ $record->title }}</h2>
                    @if($record->coach?->specialty)<p class="mt-1 text-sm text-zinc-500">{{ $record->coach->specialty }}</p>@endif
                </div>
                <time datetime="{{ $record->recorded_at->format('Y-m-d') }}" class="shrink-0 text-sm font-semibold text-zinc-500">{{ $record->recorded_at->format('M j, Y') }}</time>
            </div>
            <p class="mt-4 whitespace-pre-line text-sm leading-6 text-zinc-700">{{ $record->notes }}</p>
        </article>
    @empty
        <div class="rush-card p-8 text-center">
            <p class="font-bold text-zinc-900">No coach records yet.</p>
            <p class="mt-2 text-sm text-zinc-500">Records added by your coaches will appear here.</p>
        </div>
    @endforelse
</div>

@if($records)<div class="mt-6">{{ $records->links() }}</div>@endif
@endsection
