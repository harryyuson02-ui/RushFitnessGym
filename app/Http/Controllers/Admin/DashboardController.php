<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Coach;
use App\Models\Member;
use App\Models\Subscription;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $expiringSubscriptions = Subscription::query()
            ->with(['member.coach', 'plan'])
            ->where('status', 'active')
            ->whereBetween('expires_at', [today(), today()->addDays(3)])
            ->orderBy('expires_at')
            ->get();

        $monthInput = $request->string('attendance_month')->toString();
        $attendanceMonth = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $monthInput)
            ? CarbonImmutable::createFromFormat('!Y-m', $monthInput)
            : CarbonImmutable::now()->startOfMonth();
        $dateInput = $request->string('attendance_date')->toString();
        $selectedAttendanceDate = preg_match('/^\d{4}-(0[1-9]|1[0-2])-([0-2]\d|3[01])$/', $dateInput)
            ? CarbonImmutable::createFromFormat('!Y-m-d', $dateInput)
            : CarbonImmutable::today();

        if (! $selectedAttendanceDate->isSameMonth($attendanceMonth)) {
            $attendanceMonth = $selectedAttendanceDate->startOfMonth();
        }

        $monthAttendances = Attendance::query()
            ->with('member')
            ->whereBetween('attendance_date', [
                $attendanceMonth->startOfMonth()->toDateString(),
                $attendanceMonth->endOfMonth()->toDateString(),
            ])
            ->orderBy('checked_in_at')
            ->get()
            ->groupBy(fn (Attendance $attendance): string => $attendance->attendance_date->toDateString());

        $calendarDays = [];
        $calendarStart = $attendanceMonth->startOfMonth()->startOfWeek();
        $calendarEnd = $attendanceMonth->endOfMonth()->endOfWeek();

        for ($day = $calendarStart; $day->lte($calendarEnd); $day = $day->addDay()) {
            $calendarDays[] = [
                'date' => $day,
                'isCurrentMonth' => $day->month === $attendanceMonth->month,
                'attendances' => $monthAttendances->get($day->toDateString(), collect()),
            ];
        }

        return view('admin.dashboard', [
            'memberCount' => Member::count(),
            'activeSubscriptionCount' => Subscription::active()->count(),
            'availableCoachCount' => Coach::where('is_available', true)->count(),
            'expiringSubscriptions' => $expiringSubscriptions,
            'attendanceHistory' => Attendance::query()
                ->with('member')
                ->whereDate('attendance_date', $selectedAttendanceDate)
                ->latest('checked_in_at')
                ->limit(100)
                ->get(),
            'attendanceMonth' => $attendanceMonth,
            'calendarDays' => $calendarDays,
            'selectedAttendanceDate' => $selectedAttendanceDate,
        ]);
    }
}
