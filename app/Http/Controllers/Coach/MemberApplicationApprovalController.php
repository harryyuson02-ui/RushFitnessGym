<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewCoachAssignmentRequest;
use App\Models\CoachAssignmentRequest;
use App\Notifications\ApprovalDecision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class MemberApplicationApprovalController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ReviewCoachAssignmentRequest $request, string $assignmentRequest): RedirectResponse
    {
        $coach = $request->user()->coach;
        abort_unless($coach, 403);
        $decision = $request->validated('decision');

        $assignment = DB::transaction(function () use ($coach, $assignmentRequest, $decision): CoachAssignmentRequest {
            $lockedAssignment = CoachAssignmentRequest::query()
                ->with('member.user')
                ->whereKey($assignmentRequest)
                ->whereBelongsTo($coach)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($lockedAssignment->status === 'pending', 409, 'This training request has already been reviewed.');

            $accepted = $decision === 'accepted';
            $lockedAssignment->update([
                'status' => $decision,
                'reviewed_at' => now(),
            ]);

            if ($accepted) {
                $lockedAssignment->member->update(['coach_id' => $coach->id]);
            }

            return $lockedAssignment;
        });

        $assignment->member->user?->notify(new ApprovalDecision(
            'Training request '.$decision,
            $decision === 'accepted'
                ? 'Your coach accepted your training request. You can now view your coach records and chat.'
                : 'The coach declined your training request. You may select another available coach.',
        ));

        return redirect()->route('coach.approvals.index')->with('success', 'Training request '.$decision.'.');
    }
}
