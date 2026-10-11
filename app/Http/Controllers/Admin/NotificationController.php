<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Notifications\AttendanceCheckedIn;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function attendance(Request $request): JsonResponse
    {
        $notifications = $request->user()->unreadNotifications()
            ->where('type', AttendanceCheckedIn::class)
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'notifications' => $notifications->map(fn ($notification): array => [
                'id' => $notification->id,
                'title' => $notification->data['title'] ?? 'Attendance recorded',
                'body' => $notification->data['body'] ?? 'Your QR was scanned successfully.',
                'created_at' => $notification->created_at->toIso8601String(),
            ])->values(),
        ]);
    }

    public function update(Request $request, string $notification): RedirectResponse
    {
        $databaseNotification = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        $databaseNotification->markAsRead();

        $senderId = $databaseNotification->data['sender_id'] ?? null;

        if (is_int($senderId)) {
            return redirect()->route('chat.show', ['contact' => $senderId]);
        }

        $routeName = $databaseNotification->data['route_name'] ?? null;

        if (in_array($routeName, ['admin.approvals.index', 'coach.approvals.index', 'member.dashboard'], true)) {
            return redirect()->route($routeName);
        }

        return redirect()->route('chat.index');
    }
}
