@extends('layouts.portal')
@section('title', 'Transactions')
@section('content')
<div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
    <x-page-heading eyebrow="Financial records" title="Transaction history" description="Membership applications, cash payments, and GCash submissions." />
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
            <thead><tr><th>Date</th><th>Member</th><th>Plan</th><th>Payment</th><th>Amount</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->created_at->format('M j, Y') }}</td>
                        <td><p class="font-semibold">{{ $transaction->member->full_name }}</p><p class="text-xs text-zinc-500">{{ $transaction->member->member_code }}</p></td>
                        <td>{{ $transaction->plan?->name ?? '—' }}</td>
                        <td class="uppercase">{{ $transaction->payment_method }}@if($transaction->reference)<p class="text-xs normal-case text-zinc-500">Ref: {{ $transaction->reference }}</p>@endif</td>
                        <td class="font-semibold">₱{{ number_format($transaction->amount, 2) }}</td>
                        <td><span class="rush-badge {{ $transaction->status === 'paid' ? 'bg-lime-100 text-lime-900' : 'bg-amber-100 text-amber-900' }}">{{ ucfirst($transaction->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-10 text-center text-zinc-500">{{ $search !== '' ? 'No transactions match your search.' : 'No transactions recorded.' }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-5">{{ $transactions->links() }}</div>
@endsection
