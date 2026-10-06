<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RenewSubscriptionRequest;
use App\Models\Member;
use App\Models\MembershipPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubscriptionRenewalController extends Controller
{
    public function store(RenewSubscriptionRequest $request, Member $member): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($member, $validated): void {
            $member->subscriptions()->where('status', 'active')->update(['status' => 'renewed']);

            $subscription = $member->subscriptions()->create([
                'membership_plan_id' => $validated['membership_plan_id'],
                'starts_at' => $validated['starts_at'],
                'expires_at' => $validated['expires_at'],
                'status' => 'active',
                'access_token' => (string) Str::uuid(),
                'paid_at' => now(),
                'qr_issued_at' => now(),
            ]);
            $plan = MembershipPlan::findOrFail($validated['membership_plan_id']);
            $member->transactions()->create([
                'subscription_id' => $subscription->id,
                'membership_plan_id' => $plan->id,
                'amount' => $plan->price,
                'payment_method' => 'cash',
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        });

        return redirect()->route('admin.members.show', $member)
            ->with('success', 'Payment confirmed. A new QR access key has been issued.');
    }
}
