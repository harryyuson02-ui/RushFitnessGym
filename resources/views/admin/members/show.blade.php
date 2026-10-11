@extends('layouts.portal')

@section('title', $member->full_name)

@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <x-page-heading eyebrow="{{ $member->member_code }}" :title="$member->full_name" :description="$member->phone.' · '.($member->email ?? 'No email')" />
    <div class="flex gap-2"><a href="{{ route('admin.members.edit', $member) }}" class="inline-flex min-h-12 items-center rounded-xl border border-zinc-300 bg-white px-5 font-bold">Edit profile</a><form method="POST" action="{{ route('admin.members.destroy', $member) }}" onsubmit="return confirm('Delete this member and all subscription history?')">@csrf @method('DELETE')<button class="min-h-12 rounded-xl px-4 font-bold text-red-600">Delete</button></form></div>
</div>

<div class="mt-7 grid gap-6 xl:grid-cols-[.8fr_1.2fr]">
    <section class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-7">
        <h2 class="text-xl font-extrabold">Member profile</h2>
        <dl class="mt-5 grid gap-4 text-sm">
            <div><dt class="text-zinc-500">Assigned coach</dt><dd class="mt-1 font-bold">{{ $member->coach?->name ?? 'Unassigned' }}</dd><dd class="text-zinc-500">{{ $member->coach?->specialty }}</dd></div>
            <div><dt class="text-zinc-500">Address</dt><dd class="mt-1 font-bold">{{ $member->address ?: 'Not provided' }}</dd></div>
        </dl>
    </section>

    @if ($subscription)
        <section class="rounded-xl bg-zinc-950 p-5 text-white shadow-sm sm:p-7">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-center">
                <div class="rounded-lg bg-white p-3"><img class="size-48" src="{{ route('access-key.image', $subscription->access_token) }}" alt="QR access key for {{ $member->full_name }}"></div>
                <div class="min-w-0 flex-1"><p class="text-xs font-bold uppercase tracking-[.16em] text-lime-400">Official access key</p><h2 class="mt-2 text-2xl font-bold tracking-tight">{{ $subscription->plan->name }}</h2><p class="mt-2 text-sm text-zinc-400">Valid {{ $subscription->starts_at->format('M j, Y') }} – {{ $subscription->expires_at->format('M j, Y') }}</p><p class="mt-1 text-sm text-zinc-400">Coach: {{ $member->coach?->name ?? 'Unassigned' }}</p>
                    <div class="mt-5 flex flex-wrap gap-2"><a href="{{ $messageUrl }}" class="inline-flex min-h-12 items-center rounded-xl bg-lime-400 px-5 font-extrabold text-zinc-950" target="_blank" rel="noopener">Send access details</a><a href="{{ route('access-key.image', $subscription->access_token) }}" download="{{ $member->member_code }}-qr.svg" class="inline-flex min-h-12 items-center rounded-xl border border-zinc-700 px-5 font-bold">Download QR</a></div>
                </div>
            </div>
        </section>
    @endif
</div>

<section class="mt-6 rounded-xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-7">
    <h2 class="text-xl font-extrabold">Renew membership</h2><p class="mt-1 text-sm text-zinc-500">Payment confirmation rotates the access token and issues a new QR code.</p>
    <form method="POST" action="{{ route('admin.members.renewal.store', $member) }}" class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @csrf
        <label class="grid gap-2 text-sm font-bold">Plan<select id="renew-plan" class="min-h-12 rounded-xl border border-zinc-300 bg-white px-4 font-normal" name="membership_plan_id" required><option value="">Select</option>@foreach($plans as $plan)<option value="{{ $plan->id }}" data-days="{{ $plan->duration_days }}">{{ $plan->name }} · ₱{{ number_format($plan->price, 2) }}</option>@endforeach</select></label>
        <label class="grid gap-2 text-sm font-bold">Start<input id="renew-start" class="min-h-12 rounded-xl border border-zinc-300 px-4 font-normal" type="date" name="starts_at" value="{{ ($subscription && $subscription->expires_at->isFuture() ? $subscription->expires_at->copy()->addDay() : today())->toDateString() }}" required></label>
        <label class="grid gap-2 text-sm font-bold">End<input id="renew-end" class="min-h-12 rounded-xl border border-zinc-300 px-4 font-normal" type="date" name="expires_at" required></label>
        <div class="grid gap-2"><label class="flex min-h-12 items-center gap-2 text-sm font-bold"><input class="size-5 accent-lime-500" type="checkbox" name="payment_confirmed" value="1" required> Payment confirmed</label><button class="min-h-12 rounded-xl bg-zinc-950 px-5 font-bold text-white">Renew & reissue</button></div>
    </form>
    @if($errors->any())<div class="mt-4 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif
</section>

<section class="mt-6 rounded-xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-7"><h2 class="text-xl font-bold">Subscription history</h2><div class="mt-4 grid gap-2">@foreach($member->subscriptions->sortByDesc('id') as $item)<div class="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-zinc-50 p-4 text-sm"><div><span class="font-bold">{{ $item->plan->name }}</span><span class="ml-2 text-zinc-500">{{ $item->starts_at->format('M j, Y') }} – {{ $item->expires_at->format('M j, Y') }}</span></div><span class="rounded-full bg-white px-3 py-1 font-bold">{{ ucfirst($item->status) }}</span></div>@endforeach</div></section>

<script>
    const renewPlan = document.getElementById('renew-plan'); const renewStart = document.getElementById('renew-start'); const renewEnd = document.getElementById('renew-end');
    function setRenewEnd() { const days = Number(renewPlan.selectedOptions[0]?.dataset.days); if (!days || !renewStart.value) return; const date = new Date(renewStart.value + 'T00:00:00'); date.setDate(date.getDate() + days - 1); renewEnd.value = date.toISOString().slice(0, 10); }
    renewPlan.addEventListener('change', setRenewEnd); renewStart.addEventListener('change', setRenewEnd);
</script>
@endsection
