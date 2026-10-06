<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewApprovalRequest;
use App\Models\Subscription;
use App\Notifications\ApprovalDecision;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class GymAccessApprovalController extends Controller
{
    public function __invoke(ReviewApprovalRequest $request, string $subscription): RedirectResponse
    {
        $decision = $request->validated('decision');

        $subscription = DB::transaction(function () use ($subscription, $decision): Subscription {
            $lockedSubscription = Subscription::query()
                ->with('member.user')
                ->whereKey($subscription)
                ->whereHas('member', fn (Builder $member): Builder => $member->whereNull('coach_id'))
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($lockedSubscription->status === 'pending', 409, 'This application has already been reviewed.');

            $approved = $decision === 'approved';
            $lockedSubscription->update($approved ? [
                'status' => 'active',
                'paid_at' => now(),
                'qr_issued_at' => now(),
            ] : [
                'status' => 'declined',
            ]);
            $lockedSubscription->transactions()->update([
                'status' => $approved ? 'paid' : 'rejected',
                'paid_at' => $approved ? now() : null,
            ]);

            return $lockedSubscription;
        });

        $subscription->member->user?->notify(new ApprovalDecision(
            'Membership application '.($decision === 'approved' ? 'approved' : 'declined'),
            $decision === 'approved'
                ? 'An administrator approved your gym-only membership. Your member access is now active.'
                : 'An administrator declined your gym-only membership application. Contact the gym for assistance.',
        ));

        return redirect()->route('admin.approvals.index')->with('success', 'Gym-only membership application '.$decision.'.');
    }
}
