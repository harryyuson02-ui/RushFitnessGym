@extends('layouts.admin')

@section('title', 'Verify Access')

@section('content')
@php($isValid = $subscription->status === 'active' && $subscription->starts_at->lte(today()) && $subscription->expires_at->gte(today()))
<div class="mx-auto max-w-xl rounded-xl border {{ $isValid ? 'border-lime-300 bg-lime-50' : 'border-red-200 bg-red-50' }} p-6 text-center shadow-sm sm:p-10">
    <div class="mx-auto flex size-20 items-center justify-center rounded-full {{ $isValid ? 'bg-lime-400' : 'bg-red-500 text-white' }} text-4xl font-black">{{ $isValid ? '✓' : '×' }}</div>
    <p class="mt-6 text-xs font-bold uppercase tracking-[.18em] {{ $isValid ? 'text-lime-700' : 'text-red-700' }}">{{ $isValid ? 'Access approved' : 'Access denied' }}</p>
    <h1 class="mt-2 text-3xl font-bold tracking-tight">{{ $subscription->member->full_name }}</h1>
    <p class="mt-2 font-bold">{{ $subscription->member->member_code }}</p>
    <dl class="mt-7 grid gap-3 rounded-lg border border-zinc-200 bg-white p-5 text-left text-sm"><div class="flex justify-between gap-4"><dt class="text-zinc-500">Plan</dt><dd class="font-bold">{{ $subscription->plan->name }}</dd></div><div class="flex justify-between gap-4"><dt class="text-zinc-500">Coach</dt><dd class="font-bold">{{ $subscription->member->coach?->name ?? 'Unassigned' }}</dd></div><div class="flex justify-between gap-4"><dt class="text-zinc-500">Expires</dt><dd class="font-bold">{{ $subscription->expires_at->format('M j, Y') }}</dd></div></dl>
</div>
@endsection
