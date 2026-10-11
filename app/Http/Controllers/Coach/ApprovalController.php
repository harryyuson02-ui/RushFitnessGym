<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\CoachAssignmentRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function index(Request $request): View
    {
        $coach = $request->user()->coach;
        abort_unless($coach, 403);

        $assignmentRequests = CoachAssignmentRequest::query()
            ->with([
                'member:id,user_id,coach_id,member_code,first_name,last_name,fitness_goal,availability_days,availability_starts_at,availability_ends_at',
            ])
            ->where('status', 'pending')
            ->whereBelongsTo($coach)
            ->latest()
            ->get();

        return view('coach.approvals.index', compact('assignmentRequests'));
    }
}
