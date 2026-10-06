<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:all,pending,paid,rejected'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $status = $validated['status'] ?? 'all';
        $search = trim($validated['search'] ?? '');
        $transactions = Transaction::query()
            ->with(['member:id,first_name,last_name,member_code', 'plan:id,name'])
            ->when($status !== 'all', fn (Builder $query): Builder => $query->where('status', $status))
            ->when($search !== '', function (Builder $query) use ($search): void {
                foreach (preg_split('/\s+/', $search) ?: [] as $term) {
                    $pattern = "%{$term}%";

                    $query->where(function (Builder $query) use ($pattern): Builder {
                        return $query
                            ->whereLike('reference', $pattern)
                            ->orWhereLike('payment_method', $pattern)
                            ->orWhereHas('member', function (Builder $member) use ($pattern): Builder {
                                return $member
                                    ->whereLike('first_name', $pattern)
                                    ->orWhereLike('last_name', $pattern)
                                    ->orWhereLike('member_code', $pattern);
                            })
                            ->orWhereHas('plan', fn (Builder $plan): Builder => $plan->whereLike('name', $pattern));
                    });
                }
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.transactions.index', compact('transactions', 'status', 'search'));
    }
}
