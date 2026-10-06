<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCoachAvailabilityRequest;
use App\Models\CoachAvailabilityChange;
use App\Models\User;
use App\Notifications\ApprovalRequested;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class AvailabilityController extends Controller
{
    public function edit(Request $request): View
    {
        $coach = $request->user()->coach;
        abort_unless($coach, 403);

        return view('coach.availability', [
            'coach' => $coach->load('availabilitySlots'),
            'pendingChange' => $coach->availabilityChanges()->where('status', 'pending')->latest()->first(),
        ]);
    }

    public function update(UpdateCoachAvailabilityRequest $request): RedirectResponse
    {
        $coach = $request->user()->coach;
        abort_unless($coach, 403);
        $validated = $request->validated();

        $slots = collect($validated['slots'])
            ->filter(fn (array $slot): bool => (bool) ($slot['enabled'] ?? false))
            ->map(fn (array $slot): array => [
                'day_of_week' => (int) $slot['day_of_week'],
                'starts_at' => $slot['starts_at'],
                'ends_at' => $slot['ends_at'],
                'is_available' => true,
            ])
            ->sortBy('day_of_week')
            ->values()
            ->all();

        DB::transaction(function () use ($coach, $slots, $request): void {
            CoachAvailabilityChange::updateOrCreate(
                ['coach_id' => $coach->id, 'status' => 'pending'],
                [
                    'submitted_by' => $request->user()->id,
                    'slots' => $slots,
                    'is_available' => $request->boolean('is_available'),
                ],
            );
        });

        Notification::send(
            User::where('role', 'admin')->get(),
            new ApprovalRequested(
                'Coach schedule update',
                $coach->name.' submitted new availability for approval.',
            ),
        );

        return back()->with('success', 'Availability update sent to the administrator for approval.');
    }
}
