<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMembershipApplicationRequest;
use App\Models\Coach;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\ApprovalRequested;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class ApplicationController extends Controller
{
    public function store(StoreMembershipApplicationRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $plan = MembershipPlan::findOrFail($validated['membership_plan_id']);
        $coach = isset($validated['coach_id']) ? Coach::with('user')->findOrFail($validated['coach_id']) : null;
        $receiptPath = $request->file('receipt')?->store('receipts');
        $phone = $validated['phone_country_code'].' '.$validated['phone'];

        $subscription = DB::transaction(function () use ($request, $validated, $plan, $receiptPath, $phone): Subscription {
            [$firstName, $lastName] = [$validated['first_name'], $validated['last_name']];
            $member = Member::create([
                'user_id' => $request->user()->id,
                'member_code' => $this->nextMemberCode(),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'gender' => $validated['gender'],
                'phone' => $phone,
                'email' => $request->user()->email,
                'address' => $validated['address'],
                'preferred_channel' => $validated['preferred_channel'],
                'fitness_goal' => $validated['fitness_goal'],
                'availability_days' => $validated['availability_days'],
                'availability_starts_at' => $validated['availability_starts_at'],
                'availability_ends_at' => $validated['availability_ends_at'],
                'coach_id' => $validated['coach_id'] ?? null,
            ]);
            $subscription = $member->subscriptions()->create([
                'membership_plan_id' => $plan->id,
                'starts_at' => today(),
                'expires_at' => today()->addDays($plan->duration_days - 1),
                'status' => 'pending',
                'access_token' => (string) Str::uuid(),
                'paid_at' => now(),
                'qr_issued_at' => now(),
            ]);
            $member->transactions()->create([
                'subscription_id' => $subscription->id,
                'membership_plan_id' => $plan->id,
                'amount' => $plan->price,
                'payment_method' => $validated['payment_method'],
                'reference' => $validated['reference'] ?? null,
                'receipt_path' => $receiptPath,
                'status' => 'pending',
            ]);
            $request->user()->update([
                'name' => $firstName.' '.$lastName,
                'phone' => $phone,
                'gender' => $validated['gender'],
                'address' => $validated['address'],
                'preferred_channel' => $validated['preferred_channel'],
            ]);

            return $subscription;
        });

        if ($coach !== null) {
            $coach->user?->notify(new ApprovalRequested(
                'New membership application',
                $subscription->member->full_name.' selected you as their coach and is waiting for approval.',
                'coach.approvals.index',
            ));
        } else {
            Notification::send(
                User::where('role', 'admin')->get(),
                new ApprovalRequested(
                    'New gym-only membership application',
                    $subscription->member->full_name.' requested gym access without a coach and is waiting for approval.',
                    'admin.approvals.index',
                ),
            );
        }

        return redirect()->route('member.dashboard')->with('success', 'Membership application submitted for review.');
    }

    private function nextMemberCode(): string
    {
        do {
            $code = 'RFG-'.now()->format('ym').'-'.Str::upper(Str::random(6));
        } while (Member::where('member_code', $code)->exists());

        return $code;
    }
}
