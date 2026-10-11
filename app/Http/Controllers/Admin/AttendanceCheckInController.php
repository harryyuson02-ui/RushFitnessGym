<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceCheckInRequest;
use App\Models\Attendance;
use App\Models\Subscription;
use App\Notifications\AttendanceCheckedIn;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AttendanceCheckInController extends Controller
{
    public function __invoke(AttendanceCheckInRequest $request): JsonResponse
    {
        $token = $this->extractToken($request->validated('qr_value'));

        if ($token === null) {
            return response()->json([
                'message' => 'This is not a valid Rush Fitness Gym membership QR code.',
            ], 422);
        }

        $subscription = Subscription::query()
            ->with('member.user')
            ->active()
            ->whereDate('starts_at', '<=', today())
            ->where('access_token', $token)
            ->first();

        if ($subscription === null) {
            return response()->json([
                'message' => 'This membership QR is invalid, inactive, or expired.',
            ], 422);
        }

        [$attendance, $wasCreated] = DB::transaction(function () use ($request, $subscription): array {
            $attendance = Attendance::query()->firstOrCreate(
                [
                    'member_id' => $subscription->member_id,
                    'attendance_date' => today(),
                ],
                [
                    'subscription_id' => $subscription->id,
                    'scanned_by' => $request->user()->id,
                    'checked_in_at' => now(),
                ],
            );

            return [$attendance, $attendance->wasRecentlyCreated];
        });

        $attendance->load('member');

        if ($wasCreated && $subscription->member->user !== null) {
            $subscription->member->user->notify(new AttendanceCheckedIn($attendance));
        }

        return response()->json([
            'message' => $wasCreated
                ? "{$attendance->member->full_name} checked in successfully."
                : "{$attendance->member->full_name} was already checked in today.",
            'already_recorded' => ! $wasCreated,
            'attendance' => [
                'name' => $attendance->member->full_name,
                'date' => $attendance->attendance_date->format('M j, Y'),
                'time' => $attendance->checked_in_at->format('g:i A'),
            ],
        ], $wasCreated ? 201 : 200);
    }

    private function extractToken(string $value): ?string
    {
        $path = parse_url(trim($value), PHP_URL_PATH);

        if (! is_string($path) || ! preg_match('#^/admin/access/([A-Za-z0-9-]{20,100})/?$#', $path, $matches)) {
            return null;
        }

        return $matches[1];
    }
}
