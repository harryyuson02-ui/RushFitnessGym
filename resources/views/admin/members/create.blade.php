@extends('layouts.admin')

@section('title', 'Onboard Member')

@section('content')
<x-page-heading eyebrow="In-person enrollment" title="New member" description="Complete each step with the client at the admin desk." />

<form method="POST" action="{{ route('admin.members.store') }}" class="mt-7 grid gap-6">
    @csrf
    <section class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-7">
        <div class="mb-5 flex items-center gap-3"><span class="flex size-8 items-center justify-center rounded-full bg-zinc-950 text-sm font-bold text-white">1</span><h2 class="text-xl font-extrabold">Personal details</h2></div>
        <div class="grid gap-5 sm:grid-cols-2">
            @foreach ([['first_name','First name','text'],['last_name','Last name','text'],['birth_date','Birth date','date'],['gender','Gender','text']] as [$name,$label,$type])
                <label class="grid gap-2 text-sm font-bold">{{ $label }}<input class="min-h-12 rounded-xl border border-zinc-300 px-4 font-normal focus:border-lime-500 focus:outline-none focus:ring-4 focus:ring-lime-100" type="{{ $type }}" name="{{ $name }}" value="{{ old($name) }}" {{ in_array($name, ['first_name','last_name']) ? 'required' : '' }}><x-input-error :messages="$errors->get($name)" /></label>
            @endforeach
            <label class="grid gap-2 text-sm font-bold">Mobile number<input class="min-h-12 rounded-xl border border-zinc-300 px-4 font-normal focus:border-lime-500 focus:outline-none focus:ring-4 focus:ring-lime-100" name="phone" value="{{ old('phone') }}" inputmode="tel" required><x-input-error :messages="$errors->get('phone')" /></label>
            <label class="grid gap-2 text-sm font-bold">Email (optional)<input class="min-h-12 rounded-xl border border-zinc-300 px-4 font-normal focus:border-lime-500 focus:outline-none focus:ring-4 focus:ring-lime-100" type="email" name="email" value="{{ old('email') }}"><x-input-error :messages="$errors->get('email')" /></label>
            <label class="grid gap-2 text-sm font-bold sm:col-span-2">Address<textarea class="min-h-24 rounded-xl border border-zinc-300 p-4 font-normal focus:border-lime-500 focus:outline-none focus:ring-4 focus:ring-lime-100" name="address">{{ old('address') }}</textarea><x-input-error :messages="$errors->get('address')" /></label>
            <label class="grid gap-2 text-sm font-bold">Preferred message channel<select class="min-h-12 rounded-xl border border-zinc-300 bg-white px-4 font-normal" name="preferred_channel" required><option value="sms" @selected(old('preferred_channel') === 'sms')>SMS</option><option value="whatsapp" @selected(old('preferred_channel') === 'whatsapp')>WhatsApp</option></select></label>
        </div>
    </section>

    <section class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-7">
        <div class="mb-5 flex items-center gap-3"><span class="flex size-8 items-center justify-center rounded-full bg-zinc-950 text-sm font-bold text-white">2</span><h2 class="text-xl font-extrabold">Coach assignment</h2></div>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($coaches as $coach)
                <label class="cursor-pointer rounded-xl border border-zinc-200 p-4 has-[:checked]:border-lime-500 has-[:checked]:bg-lime-50"><input class="accent-lime-500" type="radio" name="coach_id" value="{{ $coach->id }}" @checked((string) old('coach_id') === (string) $coach->id) required><span class="ml-2 font-bold">{{ $coach->name }}</span><span class="mt-2 block text-sm text-zinc-600">{{ $coach->specialty }}</span><span class="mt-1 block text-xs text-zinc-500">{{ $coach->schedule }}</span></label>
            @endforeach
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('coach_id')" />
    </section>

    <section class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-7">
        <div class="mb-5 flex items-center gap-3"><span class="flex size-8 items-center justify-center rounded-full bg-zinc-950 text-sm font-bold text-white">3</span><h2 class="text-xl font-extrabold">Plan & payment</h2></div>
        <div class="grid gap-5 sm:grid-cols-2">
            <label class="grid gap-2 text-sm font-bold sm:col-span-2">Membership tier<select id="membership-plan" class="min-h-12 rounded-xl border border-zinc-300 bg-white px-4 font-normal" name="membership_plan_id" required><option value="">Select a plan</option>@foreach($plans as $plan)<option value="{{ $plan->id }}" data-days="{{ $plan->duration_days }}" @selected((string) old('membership_plan_id') === (string) $plan->id)>{{ $plan->name }} · ₱{{ number_format($plan->price, 2) }} · {{ $plan->duration_days }} days</option>@endforeach</select><x-input-error :messages="$errors->get('membership_plan_id')" /></label>
            <label class="grid gap-2 text-sm font-bold">Start date<input id="starts-at" class="min-h-12 rounded-xl border border-zinc-300 px-4 font-normal" type="date" name="starts_at" value="{{ old('starts_at', today()->toDateString()) }}" required></label>
            <label class="grid gap-2 text-sm font-bold">End date<input id="expires-at" class="min-h-12 rounded-xl border border-zinc-300 px-4 font-normal" type="date" name="expires_at" value="{{ old('expires_at') }}" required><x-input-error :messages="$errors->get('expires_at')" /></label>
            <label class="flex min-h-14 items-center gap-3 rounded-xl bg-zinc-100 p-4 text-sm font-bold sm:col-span-2"><input class="size-5 accent-lime-500" type="checkbox" name="payment_confirmed" value="1" @checked(old('payment_confirmed')) required> Payment received and confirmed</label>
            <x-input-error :messages="$errors->get('payment_confirmed')" />
        </div>
    </section>

    <div class="sticky bottom-20 flex gap-3 rounded-xl border border-zinc-200 bg-white/95 p-3 shadow-lg backdrop-blur lg:bottom-4 lg:ml-auto lg:w-fit"><a href="{{ route('admin.members.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg px-5 text-sm font-semibold text-zinc-600">Cancel</a><button class="min-h-11 rounded-lg bg-lime-400 px-6 text-sm font-bold" type="submit">Confirm & issue QR</button></div>
</form>

<script>
    const plan = document.getElementById('membership-plan');
    const start = document.getElementById('starts-at');
    const end = document.getElementById('expires-at');
    function setEndDate() { const days = Number(plan.selectedOptions[0]?.dataset.days); if (!days || !start.value) return; const date = new Date(start.value + 'T00:00:00'); date.setDate(date.getDate() + days - 1); end.value = date.toISOString().slice(0, 10); }
    plan.addEventListener('change', setEndDate); start.addEventListener('change', setEndDate); if (!end.value) setEndDate();
</script>
@endsection
