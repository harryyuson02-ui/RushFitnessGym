<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function update(Request $request, string $notification): RedirectResponse
    {
        $databaseNotification = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        $databaseNotification->markAsRead();

        $senderId = $databaseNotification->data['sender_id'] ?? null;

        if (is_int($senderId)) {
            return redirect()->route('chat.show', ['contact' => $senderId]);
        }

        $routeName = $databaseNotification->data['route_name'] ?? null;

        if (in_array($routeName, ['admin.approvals.index', 'coach.approvals.index'], true)) {
            return redirect()->route($routeName);
        }

        return redirect()->route('chat.index');
    }
}
