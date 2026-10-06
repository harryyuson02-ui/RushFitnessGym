@extends('layouts.portal')
@section('title', 'Membership Plans')
@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <x-page-heading eyebrow="Subscription catalog" title="Membership plans" description="Simple plans. Clear pricing." />
    <a href="{{ route('admin.plans.create') }}" class="rush-primary">+ Add plan</a>
</div>
<div class="mt-5 grid items-start gap-4 md:grid-cols-2 xl:grid-cols-3">
    @forelse($plans as $plan)
        @php($image = $plan->duration_days <= 30 ? 'monthly' : ($plan->duration_days <= 90 ? 'quarterly' : 'annual'))
        <article class="rush-card">
            <div class="relative">
                <img src="{{ asset('images/plans/'.$image.'.jpg') }}" alt="" width="960" height="640" loading="lazy" class="h-32 w-full rounded-t-2xl object-cover sm:h-44">
                <span class="rush-badge absolute right-3 top-3 {{ $plan->is_active ? 'bg-lime-100 text-lime-950' : 'bg-zinc-100 text-zinc-700' }}">{{ $plan->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
            <div class="p-4">
                <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
                    <h2 class="break-words text-lg font-bold tracking-tight">{{ $plan->name }}</h2>
                    <p class="text-xl font-bold tracking-tight">₱{{ number_format($plan->price, 2) }}</p>
                </div>
                <p class="mt-1 text-sm text-zinc-600">{{ $plan->duration_days }} days</p>
                @if($plan->description)<p class="mt-1 break-words text-sm leading-5 text-zinc-500">{{ $plan->description }}</p>@endif
                <p class="mt-3 border-t border-zinc-100 pt-3 text-xs text-zinc-500">{{ $plan->subscriptions_count }} subscription {{ Str::plural('record', $plan->subscriptions_count) }}</p>
                <div class="mt-3 flex gap-2">
                    <a href="{{ route('admin.plans.edit', $plan) }}" class="rush-secondary flex-1">Edit plan</a>
                    <details class="rush-menu relative">
                        <summary class="rush-secondary cursor-pointer text-lg" aria-label="More actions for {{ $plan->name }}">⋯</summary>
                        <form class="absolute right-0 top-full z-10 mt-2 w-44 rounded-lg border border-zinc-200 bg-white p-2 shadow-lg" method="POST" action="{{ route('admin.plans.destroy', $plan) }}" onsubmit="return confirm('Delete this unused plan?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="min-h-11 w-full rounded-md px-3 text-left text-sm font-semibold text-red-700 hover:bg-red-50">Delete plan</button>
                        </form>
                    </details>
                </div>
            </div>
        </article>
    @empty
        <div class="rush-card p-8 text-center md:col-span-2 xl:col-span-3"><p class="font-semibold">No plans yet</p><p class="mt-2 text-sm text-zinc-500">Add your first membership plan to start enrolling members.</p></div>
    @endforelse
</div>
@endsection
