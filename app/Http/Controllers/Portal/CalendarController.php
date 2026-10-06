<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ScheduleEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);
        $monthStart = Carbon::createFromFormat('Y-m-d', ($validated['month'] ?? now()->format('Y-m')).'-01')->startOfMonth();
        $gridStart = $monthStart->copy()->startOfWeek(Carbon::SUNDAY);
        $gridEnd = $monthStart->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        $eventsByDate = ScheduleEvent::query()
            ->with(['coach:id,name,specialty', 'member:id,first_name,last_name'])
            ->when($user->isCoach(), fn (Builder $query): Builder => $query->where('coach_id', $user->coach?->id ?? 0))
            ->when($user->isMember(), fn (Builder $query): Builder => $query->where('member_id', $user->member?->id ?? 0))
            ->where('type', 'session')
            ->whereNotNull('member_id')
            ->whereBetween('starts_at', [$gridStart->copy()->startOfDay(), $gridEnd->copy()->endOfDay()])
            ->orderBy('starts_at')
            ->get()
            ->groupBy(fn (ScheduleEvent $event): string => $event->starts_at->toDateString());

        $calendarDays = collect();
        for ($date = $gridStart->copy(); $date->lte($gridEnd); $date->addDay()) {
            $calendarDays->push([
                'date' => $date->copy(),
                'is_current_month' => $date->month === $monthStart->month,
                'events' => $eventsByDate->get($date->toDateString(), collect()),
            ]);
        }

        return view('portal.calendar', [
            'calendarDays' => $calendarDays,
            'monthStart' => $monthStart,
            'previousMonth' => $monthStart->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $monthStart->copy()->addMonth()->format('Y-m'),
        ]);
    }
}
