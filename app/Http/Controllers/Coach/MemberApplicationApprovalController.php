<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewApprovalRequest;
use App\Models\Subscription;
use App\Notifications\ApprovalDecision;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class MemberApplicationApprovalController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ReviewApprovalRequest $request, string $subscription): RedirectResponse
    {
        $coach = $request->user()->coach;
        abort_unless($coach, 403);
        $decision = $request->validated('decision');

        $subscription = DB::transaction(function () use ($coach, $subscription, $decision): Subscription {
            $lockedSubscription = Subscription::query()
                ->with('member.user')
                ->whereKey($subscription)
                ->whereHas('member', fn (Builder $query): Builder => $query->whereBelongsTo($coach))
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
                ? 'Your selected coach approved your membership. Your member access is now active.'
                : 'Your selected coach declined your membership application. Open chat to contact your coach for assistance.',
        ));

        return redirect()->route('coach.approvals.index')->with('success', 'Member application '.$decision.'.');
    }
}
