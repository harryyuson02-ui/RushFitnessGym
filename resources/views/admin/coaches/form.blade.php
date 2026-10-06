@php
    $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    $submittedAvailability = collect(old('availability', []));
    $hasSubmittedAvailability = old('availability') !== null;
    $storedAvailability = $coach?->availabilitySlots ?? collect();
    $useWeekdayDefaults = ! $coach || $storedAvailability->isEmpty();
@endphp

<div class="grid gap-5 sm:grid-cols-2">
    <label class="rush-label">Full name<input class="rush-input" name="name" value="{{ old('name', $coach?->name) }}" required><x-input-error :messages="$errors->get('name')" /></label>
    <label class="rush-label">Mobile number
        <input class="rush-input" type="tel" name="phone" value="{{ old('phone', $coach?->phone) }}" inputmode="numeric" autocomplete="tel-national" pattern="09[0-9]{9}" placeholder="09171234567" title="Enter a Philippine mobile number with exactly 11 digits starting with 09 (e.g. 09171234567)." aria-describedby="coach-phone-help coach-phone-error" data-ph-mobile required>
        <span id="coach-phone-help" class="text-xs font-normal text-zinc-500">Use 09XXXXXXXXX: exactly 11 digits, without spaces, letters, or symbols.</span>
        <span id="coach-phone-error" class="text-xs font-normal text-red-600" aria-live="polite" hidden></span>
        <x-input-error :messages="$errors->get('phone')" />
    </label>
    <label class="rush-label">Email<input class="rush-input" type="email" name="email" value="{{ old('email', $coach?->email) }}" @required(! $coach)>@if (! $coach)<span class="text-xs font-normal text-zinc-500">Used to sign in. A temporary password will be generated when you save.</span>@endif<x-input-error :messages="$errors->get('email')" /></label>
    <label class="rush-label">Specialty<select class="rush-input" name="specialty" required><option value="">Select specialty</option>@foreach(\App\Models\Coach::SPECIALTIES as $specialty)<option value="{{ $specialty }}" @selected(old('specialty', $coach?->specialty) === $specialty)>{{ $specialty }}</option>@endforeach</select><x-input-error :messages="$errors->get('specialty')" /></label>
</div>

<fieldset class="mt-7">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div><legend class="text-lg font-bold text-zinc-950">Weekly schedule calendar</legend><p class="mt-1 text-sm text-zinc-500">Select each working day, then choose the start and end time.</p></div>
        <span class="rush-badge bg-lime-100 text-lime-900">Recurring weekly</span>
    </div>
    <div class="mt-4 grid gap-3 lg:grid-cols-2">
        @foreach ($dayNames as $dayNumber => $dayName)
            @php
                $submittedSlot = $submittedAvailability->first(fn (mixed $slot): bool => is_array($slot) && (int) ($slot['day_of_week'] ?? -1) === $dayNumber);
                $storedSlot = $storedAvailability->firstWhere('day_of_week', $dayNumber);
                $isEnabled = $hasSubmittedAvailability
                    ? (bool) ($submittedSlot['enabled'] ?? false)
                    : ($storedSlot !== null || ($useWeekdayDefaults && in_array($dayNumber, [1, 2, 3, 4, 5], true)));
                $startsAt = $submittedSlot['starts_at'] ?? ($storedSlot?->starts_at ? substr($storedSlot->starts_at, 0, 5) : '08:00');
                $endsAt = $submittedSlot['ends_at'] ?? ($storedSlot?->ends_at ? substr($storedSlot->ends_at, 0, 5) : '17:00');
            @endphp
            <div class="grid gap-4 rounded-xl border border-zinc-200 bg-zinc-50 p-4 transition has-[:checked]:border-lime-400 has-[:checked]:bg-lime-50 sm:grid-cols-[8rem_1fr_1fr] sm:items-end">
                <label class="flex min-h-12 items-center gap-3 font-bold">
                    <input type="hidden" name="availability[{{ $dayNumber }}][day_of_week]" value="{{ $dayNumber }}">
                    <input class="size-5 accent-lime-500" type="checkbox" name="availability[{{ $dayNumber }}][enabled]" value="1" @checked($isEnabled)>
                    {{ $dayName }}
                </label>
                <label class="rush-label">Starts<input class="rush-input bg-white" type="time" name="availability[{{ $dayNumber }}][starts_at]" value="{{ $startsAt }}" required></label>
                <label class="rush-label">Ends<input class="rush-input bg-white" type="time" name="availability[{{ $dayNumber }}][ends_at]" value="{{ $endsAt }}" required></label>
            </div>
        @endforeach
    </div>
    <x-input-error :messages="$errors->get('availability')" />
    <x-input-error :messages="$errors->get('availability.*')" />
</fieldset>

<label class="mt-6 flex min-h-12 items-center gap-3 rounded-xl bg-zinc-100 p-4 text-sm font-bold"><input type="hidden" name="is_available" value="0"><input class="size-5 accent-lime-500" type="checkbox" name="is_available" value="1" @checked(old('is_available', $coach?->is_available ?? true))> Available for new assignments</label>
<div class="mt-7 flex justify-end gap-3"><a href="{{ route('admin.coaches.index') }}" class="inline-flex min-h-12 items-center px-5 font-bold text-zinc-600">Cancel</a><button class="min-h-12 rounded-xl bg-lime-400 px-6 font-extrabold">Save coach</button></div>
