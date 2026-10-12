<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CoachAvailabilityChange;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($validated['search'] ?? '');
        $availabilityChanges = CoachAvailabilityChange::query()
            ->with(['coach:id,user_id,name,specialty,schedule', 'submitter:id,name'])
            ->where('status', 'pending')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->whereHas('coach', fn (Builder $coach): Builder => $coach->where('name', 'like', $search.'%'));
            })
            ->latest()
            ->get();
        $memberApplications = Subscription::query()
            ->with([
                'member:id,user_id,coach_id,member_code,first_name,last_name,fitness_goal',
                'plan:id,name,price',
                'requestedCoach:id,name',
                'transactions:id,subscription_id,amount,payment_method,reference,receipt_path,status',
                'transactions.gcashPayment:id,transaction_id,reference,receipt_path,verification_status,verified_by,verified_at,verification_notes',
                'transactions.gcashPayment.verifier:id,name',
                'transactions.cashPayment:id,transaction_id,received_by,received_at',
            ])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('admin.approvals.index', compact('availabilityChanges', 'memberApplications', 'search'));
    }
}
