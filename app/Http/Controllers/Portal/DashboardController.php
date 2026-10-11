<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\ScheduleEvent;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function coach(Request $request): View
    {
        $coach = request()->user()->coach;
        $events = $coach ? ScheduleEvent::with('member')->whereBelongsTo($coach)->where('starts_at', '>=', now())->orderBy('starts_at')->limit(5)->get() : collect();
        $monthInput = $request->string('schedule_month')->toString();
        $calendarMonth = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $monthInput)
            ? CarbonImmutable::createFromFormat('!Y-m', $monthInput)
            : CarbonImmutable::now()->startOfMonth();
        $calendarStart = $calendarMonth->startOfMonth()->startOfWeek(CarbonInterface::SUNDAY);
        $calendarEnd = $calendarMonth->endOfMonth()->endOfWeek(CarbonInterface::SATURDAY);
        $calendarEvents = $coach
            ? ScheduleEvent::query()
                ->with('member')
                ->whereBelongsTo($coach)
                ->where('type', 'session')
                ->whereNotNull('member_id')
                ->whereBetween('starts_at', [$calendarStart->startOfDay(), $calendarEnd->endOfDay()])
                ->orderBy('starts_at')
                ->get()
                ->groupBy(fn (ScheduleEvent $event): string => $event->starts_at->toDateString())
            : collect();
        $calendarDays = [];

        for ($date = $calendarStart; $date->lte($calendarEnd); $date = $date->addDay()) {
            $calendarDays[] = [
                'date' => $date,
                'isCurrentMonth' => $date->month === $calendarMonth->month,
                'events' => $calendarEvents->get($date->toDateString(), collect()),
            ];
        }

        return view('coach.dashboard', [
            'coach' => $coach,
            'clientCount' => $coach ? Member::activeClientOf($coach)->count() : 0,
            'upcomingEvents' => $events,
            'calendarMonth' => $calendarMonth,
            'calendarDays' => $calendarDays,
        ]);
    }

    public function member(): View
    {
        $member = request()->user()->member?->load(['coach', 'currentSubscription.plan', 'pendingCoachAssignment.coach']);
        $events = $member ? ScheduleEvent::with('coach')->whereBelongsTo($member)->where('starts_at', '>=', now())->orderBy('starts_at')->limit(5)->get() : collect();
        $activeSubscription = $member?->subscriptions()->with('plan')->active()->latest('id')->first();
        $canApply = $member === null || $member->canApplyForMembership();
        $canRequestCoach = $member !== null
            && $member->coach_id === null
            && $member->pendingCoachAssignment === null
            && $member->subscriptions()->active()->exists();

        return view('member.dashboard', [
            'member' => $member,
            'events' => $events,
            'activeSubscription' => $activeSubscription,
            'canApply' => $canApply,
            'coaches' => $canApply ? Coach::where('is_available', true)->whereNotNull('user_id')->orderBy('specialty')->orderBy('name')->get() : collect(),
            'availableCoaches' => $canRequestCoach ? Coach::where('is_available', true)->whereNotNull('user_id')->orderBy('specialty')->orderBy('name')->get() : collect(),
            'plans' => $canApply ? MembershipPlan::where('is_active', true)->orderBy('price')->get() : collect(),
        ]);
    }
}
