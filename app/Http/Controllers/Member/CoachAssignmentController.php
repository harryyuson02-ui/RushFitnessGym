<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCoachAssignmentRequest;
use App\Models\Coach;
use App\Models\CoachAssignmentRequest;
use App\Models\Member;
use App\Notifications\ApprovalRequested;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class CoachAssignmentController extends Controller
{
    public function store(StoreCoachAssignmentRequest $request): RedirectResponse
    {
        $coach = Coach::with('user')->findOrFail($request->validated('coach_id'));

        $assignment = DB::transaction(function () use ($request, $coach): CoachAssignmentRequest {
            $member = Member::query()
                ->whereBelongsTo($request->user())
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                $member->coach_id === null
                    && $member->subscriptions()->active()->exists()
                    && ! $member->coachAssignmentRequests()->where('status', 'pending')->exists(),
                409,
                'You already have a coach or a pending training request.',
            );

            return $member->coachAssignmentRequests()->create([
                'coach_id' => $coach->id,
                'status' => 'pending',
            ]);
        });

        $coach->user?->notify(new ApprovalRequested(
            'New training request',
            $assignment->member->full_name.' requested you as their coach.',
            'coach.approvals.index',
        ));

        return redirect()->route('member.dashboard')->with('success', 'Training request sent to '.$coach->name.'.');
    }
}
