@extends('layouts.portal')
@section('title', 'Availability')
@section('content')
<x-page-heading eyebrow="Weekly settings" title="Coach availability" description="Propose new working hours for administrator approval." />
@php
    $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    $submittedSlots = collect(old('slots', []));
    $hasSubmittedSlots = old('slots') !== null;
    $displaySlots = $pendingChange ? collect($pendingChange->slots) : $coach->availabilitySlots;
    $useWeekdayDefaults = $displaySlots->isEmpty();
@endphp
@if($pendingChange)
    <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900"><strong>Awaiting admin review.</strong> The calendar below shows your latest submitted update. Your current approved schedule remains active until it is approved.</div>
@endif
<form method="POST" action="{{ route('coach.availability.update') }}" class="rush-card mt-6 p-5 sm:p-7">
    @csrf
    @method('PUT')
    <label class="mb-6 flex items-center gap-3 rounded-xl bg-lime-50 p-4 font-semibold"><input type="hidden" name="is_available" value="0"><input class="size-5 accent-lime-500" type="checkbox" name="is_available" value="1" @checked(old('is_available', $pendingChange?->is_available ?? $coach->is_available))> Accepting new clients</label>
    <div class="grid gap-3 lg:grid-cols-2">
        @foreach($dayNames as $dayNumber => $dayName)
            @php
                $submittedSlot = $submittedSlots->first(fn (mixed $slot): bool => is_array($slot) && (int) ($slot['day_of_week'] ?? -1) === $dayNumber);
                $savedSlot = $displaySlots->first(fn (mixed $slot): bool => (int) data_get($slot, 'day_of_week', -1) === $dayNumber);
                $isEnabled = $hasSubmittedSlots
                    ? (bool) ($submittedSlot['enabled'] ?? false)
                    : ($savedSlot !== null || ($useWeekdayDefaults && in_array($dayNumber, [1, 2, 3, 4, 5], true)));
                $startsAt = $submittedSlot['starts_at'] ?? data_get($savedSlot, 'starts_at', '08:00');
                $endsAt = $submittedSlot['ends_at'] ?? data_get($savedSlot, 'ends_at', '17:00');
            @endphp
            <div class="grid items-end gap-3 rounded-xl border border-zinc-200 bg-zinc-50 p-4 has-[:checked]:border-lime-400 has-[:checked]:bg-lime-50 sm:grid-cols-[8rem_1fr_1fr]">
                <label class="flex min-h-12 items-center gap-3 font-semibold"><input type="hidden" name="slots[{{ $dayNumber }}][day_of_week]" value="{{ $dayNumber }}"><input class="size-5 accent-lime-500" type="checkbox" name="slots[{{ $dayNumber }}][enabled]" value="1" @checked($isEnabled)>{{ $dayName }}</label>
                <label class="rush-label">Starts<input class="rush-input bg-white" type="time" name="slots[{{ $dayNumber }}][starts_at]" value="{{ substr((string) $startsAt, 0, 5) }}" required></label>
                <label class="rush-label">Ends<input class="rush-input bg-white" type="time" name="slots[{{ $dayNumber }}][ends_at]" value="{{ substr((string) $endsAt, 0, 5) }}" required></label>
            </div>
        @endforeach
    </div>
    <x-input-error :messages="$errors->all()" />
    <div class="mt-6 flex justify-end"><button class="rush-primary" type="submit">Submit schedule for approval</button></div>
</form>
@endsection
