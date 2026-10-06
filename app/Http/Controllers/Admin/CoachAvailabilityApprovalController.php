<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewApprovalRequest;
use App\Models\Coach;
use App\Models\CoachAvailabilityChange;
use App\Notifications\ApprovalDecision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class CoachAvailabilityApprovalController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ReviewApprovalRequest $request, CoachAvailabilityChange $availabilityChange): RedirectResponse
    {
        $decision = $request->validated('decision');

        $availabilityChange = DB::transaction(function () use ($availabilityChange, $decision, $request): CoachAvailabilityChange {
            $lockedChange = CoachAvailabilityChange::query()
                ->with('coach.user')
                ->whereKey($availabilityChange->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($lockedChange->status === 'pending', 409, 'This schedule change has already been reviewed.');

            if ($decision === 'approved') {
                $lockedChange->coach->update([
                    'is_available' => $lockedChange->is_available,
                    'schedule' => Coach::scheduleSummary($lockedChange->slots),
                ]);
                $lockedChange->coach->availabilitySlots()->delete();
                $lockedChange->coach->availabilitySlots()->createMany($lockedChange->slots);
            }

            $lockedChange->update([
                'status' => $decision,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            return $lockedChange;
        });

        $availabilityChange->coach->user?->notify(new ApprovalDecision(
            'Schedule change '.$decision,
            $decision === 'approved'
                ? 'Your updated availability is now your official schedule.'
                : 'Your availability update was declined. Your previous schedule remains active.',
        ));

        return back()->with('success', 'Coach schedule change '.$decision.'.');
    }
}
