<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewApprovalRequest;
use App\Models\CoachAssignmentRequest;
use App\Models\Subscription;
use App\Notifications\ApprovalDecision;
use App\Notifications\ApprovalRequested;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GymAccessApprovalController extends Controller
{
    public function __invoke(ReviewApprovalRequest $request, string $subscription): RedirectResponse
    {
        $decision = $request->validated('decision');

        $subscription = DB::transaction(function () use ($request, $subscription, $decision): Subscription {
            $lockedSubscription = Subscription::query()
                ->with(['member.user', 'requestedCoach.user', 'transactions.gcashPayment', 'transactions.cashPayment'])
                ->whereKey($subscription)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($lockedSubscription->status === 'pending', 409, 'This application has already been reviewed.');

            $approved = $decision === 'approved';
            $payment = $lockedSubscription->transactions->first();

            abort_if(
                $approved
                    && $payment?->payment_method === 'gcash'
                    && $payment->gcashPayment?->verification_status !== 'verified',
                409,
                'Verify the GCash payment before approving this membership.',
            );

            $lockedSubscription->update($approved ? [
                'status' => 'active',
                'access_token' => (string) Str::uuid(),
                'paid_at' => now(),
                'qr_issued_at' => now(),
            ] : [
                'status' => 'declined',
            ]);
            if ($approved && $payment?->payment_method === 'cash') {
                $payment->update(['status' => 'paid', 'paid_at' => now()]);
                $payment->cashPayment?->update([
                    'received_by' => $request->user()->id,
                    'received_at' => now(),
                ]);
            } elseif (! $approved) {
                $lockedSubscription->transactions()->where('status', 'pending')->update([
                    'status' => 'rejected',
                    'paid_at' => null,
                ]);
            }

            if ($approved && $lockedSubscription->requested_coach_id !== null && $lockedSubscription->member->coach_id === null) {
                CoachAssignmentRequest::firstOrCreate([
                    'member_id' => $lockedSubscription->member_id,
                    'status' => 'pending',
                ], [
                    'coach_id' => $lockedSubscription->requested_coach_id,
                ]);
            }

            return $lockedSubscription;
        });

        $subscription->member->user?->notify(new ApprovalDecision(
            'Membership application '.($decision === 'approved' ? 'approved' : 'declined'),
            $decision === 'approved'
                ? 'Your membership is active. Your personalized QR access key is ready to view and download from Overview.'
                : 'An administrator declined your membership application. Contact the gym for assistance.',
            $decision === 'approved' ? 'member.dashboard' : null,
        ));

        if ($decision === 'approved' && $subscription->requestedCoach?->user !== null) {
            $subscription->requestedCoach->user->notify(new ApprovalRequested(
                'New training request',
                $subscription->member->full_name.' requested you as their coach.',
                'coach.approvals.index',
            ));
        }

        return redirect()->route('admin.approvals.index')->with('success', 'Membership application '.$decision.'.');
    }
}
