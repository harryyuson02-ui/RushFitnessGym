<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use App\Models\MembershipPlan;
use App\Models\ScheduleEvent;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function coach(): View
    {
        $coach = request()->user()->coach;
        $events = $coach ? ScheduleEvent::with('member')->whereBelongsTo($coach)->where('starts_at', '>=', now())->orderBy('starts_at')->limit(5)->get() : collect();

        return view('coach.dashboard', [
            'coach' => $coach,
            'clientCount' => $coach?->members()->count() ?? 0,
            'upcomingEvents' => $events,
        ]);
    }

    public function member(): View
    {
        $member = request()->user()->member?->load(['coach', 'currentSubscription.plan']);
        $events = $member ? ScheduleEvent::with('coach')->whereBelongsTo($member)->where('starts_at', '>=', now())->orderBy('starts_at')->limit(5)->get() : collect();

        return view('member.dashboard', [
            'member' => $member,
            'events' => $events,
            'coaches' => $member ? collect() : Coach::where('is_available', true)->whereNotNull('user_id')->orderBy('specialty')->orderBy('name')->get(),
            'plans' => $member ? collect() : MembershipPlan::where('is_active', true)->orderBy('price')->get(),
        ]);
    }
}
