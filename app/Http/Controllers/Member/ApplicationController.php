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
        $requestedCoach = isset($validated['coach_id']) ? Coach::findOrFail($validated['coach_id']) : null;
        $receiptPath = $request->file('receipt')?->store('receipts', config('filesystems.default'));

        if ($request->hasFile('receipt') && ! is_string($receiptPath)) {
            abort(500, 'The payment receipt could not be stored.');
        }
        $phone = $validated['phone_country_code'].' '.$validated['phone'];

        $subscription = DB::transaction(function () use ($request, $validated, $plan, $requestedCoach, $receiptPath, $phone): Subscription {
            [$firstName, $lastName] = [$validated['first_name'], $validated['last_name']];
            $profile = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'gender' => $validated['gender'],
                'phone' => $phone,
                'email' => $request->user()->email,
                'address' => $validated['address'],
                'preferred_channel' => 'private_message',
                'fitness_goal' => $validated['fitness_goal'],
                'availability_days' => $validated['availability_days'],
                'availability_starts_at' => $validated['availability_starts_at'],
                'availability_ends_at' => $validated['availability_ends_at'],
            ];
            $member = $request->user()->member;

            if ($member) {
                $member->update($profile);
            } else {
                $member = Member::create([
                    ...$profile,
                    'user_id' => $request->user()->id,
                    'member_code' => $this->nextMemberCode(),
                ]);
            }
            $subscription = $member->subscriptions()->create([
                'membership_plan_id' => $plan->id,
                'requested_coach_id' => $requestedCoach?->id,
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
                'preferred_channel' => 'private_message',
            ]);

            return $subscription;
        });

        Notification::send(
            User::where('role', 'admin')->get(),
            new ApprovalRequested(
                'New membership application',
                $subscription->member->full_name.' submitted a membership and payment for review.',
                'admin.approvals.index',
            ),
        );

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
