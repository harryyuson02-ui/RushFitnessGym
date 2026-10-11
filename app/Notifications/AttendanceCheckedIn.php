<?php

namespace App\Notifications;

use App\Models\Attendance;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AttendanceCheckedIn extends Notification
{
    use Queueable;

    public function __construct(public Attendance $attendance) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Attendance recorded',
            'body' => 'Your QR was scanned successfully at '.$this->attendance->checked_in_at->format('g:i A').' on '.$this->attendance->attendance_date->format('M j, Y').'.',
            'route_name' => 'member.dashboard',
            'attendance_id' => $this->attendance->id,
        ];
    }
}
