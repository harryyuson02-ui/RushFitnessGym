@extends('layouts.portal')
@section('title', 'GCash Payment Verification')
@section('content')
<div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
    <x-page-heading eyebrow="GCash only" title="GCash payment verification" description="This queue contains only GCash submissions. Cash payments are handled separately during membership approval and never require GCash verification." />
    <form method="GET" class="flex w-full flex-col gap-2 sm:flex-row lg:w-auto" role="search">
        <label class="min-w-0 flex-1 lg:w-80">
            <span class="sr-only">Search transactions</span>
            <input class="rush-input w-full" type="search" name="search" value="{{ $search }}" placeholder="Search member, code, reference, or plan">
        </label>
        <label>
            <span class="sr-only">Filter by status</span>
            <select class="rush-input w-full sm:w-auto" name="status" onchange="this.form.submit()">
                <option value="all">All statuses</option>
                @foreach(['pending', 'paid', 'rejected'] as $value)
                    <option value="{{ $value }}" @selected($status === $value)>{{ ucfirst($value) }}</option>
                @endforeach
            </select>
        </label>
        <button class="rush-primary" type="submit">Search</button>
        @if ($search !== '' || $status !== 'all')
            <a class="rush-secondary text-center" href="{{ route('admin.transactions.index') }}">Clear</a>
        @endif
    </form>
</div>

<x-input-error class="mt-4" :messages="$errors->all()" />

<div class="rush-card mt-6 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="rush-table">
            <thead><tr><th>Date</th><th>Member</th><th>Plan and amount</th><th>Payment proof</th><th>Status</th><th>Verification</th></tr></thead>
            <tbody>
                @forelse($transactions as $transaction)
                    @php($gcash = $transaction->gcashPayment)
                    <tr>
                        <td>{{ $transaction->created_at->format('M j, Y') }}</td>
                        <td><p class="font-semibold">{{ $transaction->member->full_name }}</p><p class="text-xs text-zinc-500">{{ $transaction->member->member_code }}</p></td>
                        <td><p class="font-semibold">{{ $transaction->plan?->name ?? '—' }}</p><p class="text-xs text-zinc-500">Gym membership</p><p class="mt-1 font-bold">Total: ₱{{ number_format($transaction->amount, 2) }}</p></td>
                        <td class="uppercase">{{ $transaction->payment_method }}@if($gcash?->reference)<p class="text-xs normal-case text-zinc-500">Ref: {{ $gcash->reference }}</p>@endif @if($gcash?->receipt_path)<a class="mt-1 inline-flex text-xs font-bold normal-case text-lime-700 hover:text-lime-800" href="{{ route('admin.transactions.receipt', $transaction) }}" target="_blank" rel="noopener">View receipt</a>@else<p class="mt-1 text-xs font-semibold normal-case text-red-600">Receipt unavailable</p>@endif</td>
                        <td><span class="rush-badge {{ $gcash?->verification_status === 'verified' ? 'bg-lime-100 text-lime-900' : ($gcash?->verification_status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-900') }}">{{ ucfirst($gcash?->verification_status ?? 'missing') }}</span>@if($gcash?->verified_at)<p class="mt-2 text-xs text-zinc-500">{{ $gcash->verifier?->name ?? 'Administrator' }}<br>{{ $gcash->verified_at->format('M j, Y g:i A') }}</p>@endif</td>
                        <td class="min-w-72">@if($gcash?->verification_status === 'pending')<form method="POST" action="{{ route('admin.transactions.verification.update', $transaction) }}" class="grid gap-2">@csrf @method('PATCH')<label class="text-xs font-semibold text-zinc-600">Verification notes<textarea class="rush-input mt-1 min-h-20" name="verification_notes" maxlength="1000" placeholder="Required when rejecting"></textarea></label><div class="grid grid-cols-2 gap-2"><button class="rush-secondary text-red-700" type="submit" name="decision" value="rejected">Reject</button><button class="rush-primary" type="submit" name="decision" value="verified" @disabled(!$gcash->reference || !$gcash->receipt_path)>Verify paid</button></div></form>@elseif($gcash?->verification_notes)<p class="text-sm text-zinc-600">{{ $gcash->verification_notes }}</p>@else<span class="text-sm text-zinc-400">No action required</span>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-10 text-center text-zinc-500">{{ $search !== '' ? 'No GCash submissions match your search.' : 'No GCash payments are waiting or recorded.' }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-5">{{ $transactions->links() }}</div>
@endsection
