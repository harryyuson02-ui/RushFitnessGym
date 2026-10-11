@extends('layouts.portal')

@section('title', 'Admin Dashboard')

@section('content')

<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-[0.7rem] font-bold uppercase tracking-[0.16em] text-lime-700">Operations overview</p>
        <h1 class="mt-1.5 text-3xl font-bold tracking-tight text-zinc-950 sm:text-4xl">Good day, Admin</h1>
        <p class="mt-2 text-sm leading-6 text-zinc-500 sm:text-base">Everything requiring attention at the front desk.</p>
    </div>
</div>

<div class="mt-8 grid gap-4 sm:grid-cols-3">
    <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm"><div class="flex items-center justify-between"><p class="text-sm font-medium text-zinc-500">Total members</p><span class="size-2 rounded-full bg-lime-500"></span></div><p class="mt-3 text-3xl font-bold tracking-tight text-zinc-950">{{ $memberCount }}</p></div>
    <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm"><div class="flex items-center justify-between"><p class="text-sm font-medium text-zinc-500">Active subscriptions</p><span class="size-2 rounded-full bg-lime-500"></span></div><p class="mt-3 text-3xl font-bold tracking-tight text-zinc-950">{{ $activeSubscriptionCount }}</p></div>
    <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm"><div class="flex items-center justify-between"><p class="text-sm font-medium text-zinc-500">Available coaches</p><span class="size-2 rounded-full bg-lime-500"></span></div><p class="mt-3 text-3xl font-bold tracking-tight text-zinc-950">{{ $availableCoachCount }}</p></div>
</div>

<div class="mt-7 grid gap-6">
    <div class="fixed bottom-24 left-1/2 z-40 -translate-x-1/2 lg:bottom-8 lg:left-[calc(50%+8.5rem)]" data-attendance-scanner data-check-in-url="{{ route('admin.attendance.check-in') }}" data-csrf-token="{{ csrf_token() }}">
        <button type="button" class="group flex size-16 items-center justify-center rounded-full bg-lime-400 text-zinc-950 shadow-[0_10px_30px_rgba(77,124,15,.35)] transition duration-200 hover:-translate-y-1 hover:bg-lime-300 hover:shadow-[0_16px_35px_rgba(77,124,15,.45)] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-lime-600" data-attendance-scan-open aria-label="Scan member attendance QR" title="Scan member attendance QR">
            <svg class="size-7 transition group-hover:scale-105" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 9V5a1 1 0 0 1 1-1h4M15 4h4a1 1 0 0 1 1 1v4M20 15v4a1 1 0 0 1-1 1h-4M9 20H5a1 1 0 0 1-1-1v-4M8 8h3v3H8V8Zm5 0h3v3h-3V8Zm-5 5h3v3H8v-3Zm5 0h1m2 0v3h-3v-1"/></svg>
        </button>
        <p class="sr-only" data-attendance-status aria-live="polite">Ready to scan a gym-issued membership QR.</p>
        <dialog class="m-auto w-[min(42rem,calc(100vw-1rem))] rounded-2xl bg-zinc-950 p-0 text-white shadow-2xl backdrop:bg-zinc-950/80" data-attendance-scan-dialog>
            <div class="p-5 sm:p-6">
                <div class="mb-4 flex items-start justify-between gap-4">
                    <div><h3 class="text-xl font-extrabold">Attendance QR scanner</h3><p class="mt-1 text-sm text-zinc-400">Hold the member’s QR inside the camera frame.</p></div>
                    <button type="button" class="rounded-lg px-3 py-2 font-bold text-zinc-300 hover:bg-zinc-800" data-attendance-scan-close aria-label="Close QR scanner">✕</button>
                </div>
                <div class="relative mx-auto aspect-[4/3] max-h-[62dvh] w-full overflow-hidden rounded-2xl border border-zinc-700 bg-black" data-attendance-camera-frame>
                    <video class="size-full object-cover" autoplay playsinline muted data-attendance-scan-video aria-label="Rear camera QR scanner"></video>
                    <div class="pointer-events-none absolute inset-x-[10%] inset-y-[16%] rounded-2xl border-2 border-lime-400 shadow-[0_0_0_999px_rgba(0,0,0,.5)]" aria-hidden="true">
                        <span class="absolute left-3 right-3 top-1/2 h-0.5 -translate-y-1/2 animate-pulse bg-lime-300 shadow-[0_0_12px_rgba(190,242,100,.9)]"></span>
                    </div>
                    <span class="pointer-events-none absolute left-1/2 top-3 -translate-x-1/2 rounded-full bg-zinc-950/80 px-3 py-1 text-[0.65rem] font-bold uppercase tracking-[.14em] text-lime-300">Automatic QR detection</span>
                    <button type="button" class="absolute inset-0 z-10 flex flex-col items-center justify-center gap-3 bg-zinc-950/85 p-6 text-center" data-attendance-camera-fallback hidden>
                        <span class="flex size-16 items-center justify-center rounded-full bg-lime-400 text-zinc-950 shadow-lg">
                            <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h3l1.4-2h7.2L17 7h3a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2Zm8 10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg>
                        </span>
                        <span class="text-lg font-extrabold text-white">Open phone camera</span>
                        <span class="max-w-xs text-sm leading-6 text-zinc-300">Photograph the QR inside the frame. It will be read automatically.</span>
                    </button>
                </div>
                <p class="mt-3 text-sm text-lime-300" data-attendance-scan-message aria-live="polite">Starting camera…</p>
                <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <label class="rush-dark-button cursor-pointer text-center">Take a QR photo instead<input type="file" accept="image/*" capture="environment" class="sr-only" data-attendance-scan-file data-mobile-camera-fallback></label>
                    <button type="button" class="rush-secondary !text-zinc-950" data-attendance-scan-cancel>Cancel</button>
                </div>
            </div>
        </dialog>
    </div>

    <section class="rounded-xl border border-zinc-200 bg-white shadow-sm" aria-labelledby="attendance-table-title">
        <div class="flex items-center justify-between gap-4 border-b border-zinc-200 p-5 sm:px-6">
            <div><p class="text-xs font-bold uppercase tracking-[0.16em] text-lime-700">Scan history</p><h2 id="attendance-table-title" class="mt-1 text-xl font-extrabold">Attendance</h2></div>
            <div class="flex items-center gap-2">
                <span class="rush-badge bg-lime-100 text-lime-900">{{ $selectedAttendanceDate->isToday() ? 'Today · ' : '' }}{{ $selectedAttendanceDate->format('M j, Y') }}</span>
                <button type="button" class="rush-secondary !size-11 !px-0" data-attendance-calendar-open aria-label="Open attendance history calendar" title="Attendance history calendar">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 2v3m12-3v3M3 9h18M5 4h14a2 2 0 0 1 2 2v14H3V6a2 2 0 0 1 2-2Zm2 9h2m3 0h2m3 0h2M7 17h2m3 0h2"/></svg>
                </button>
            </div>
        </div>
        <div class="max-h-[28rem] overflow-auto" data-attendance-table>
            <table class="w-full min-w-[34rem] text-left text-sm">
                <thead class="sticky top-0 z-10 bg-zinc-50 text-xs uppercase tracking-[0.1em] text-zinc-500">
                    <tr><th class="px-5 py-3 sm:px-6">Name</th><th class="px-5 py-3">Date</th><th class="px-5 py-3 sm:pr-6">Time</th></tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($attendanceHistory as $attendance)
                        <tr>
                            <td class="px-5 py-4 font-bold text-zinc-950 sm:px-6">{{ $attendance->member->full_name }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-zinc-600"><time datetime="{{ $attendance->attendance_date->toDateString() }}">{{ $attendance->attendance_date->format('M j, Y') }}</time></td>
                            <td class="whitespace-nowrap px-5 py-4 text-zinc-600 sm:pr-6"><time datetime="{{ $attendance->checked_in_at->format('c') }}">{{ $attendance->checked_in_at->format('g:i A') }}</time></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-6 py-12 text-center text-zinc-500">No members scanned their QR on this date.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @php($defaultCalendarDate = $selectedAttendanceDate ?? ($attendanceMonth->isSameMonth(now()) ? now()->toImmutable() : $attendanceMonth->startOfMonth()))
        <dialog class="m-auto w-[min(36rem,calc(100vw-2rem))] rounded-2xl bg-white p-0 text-zinc-950 shadow-2xl backdrop:bg-zinc-950/70" data-attendance-calendar-dialog>
            <div class="max-h-[calc(100dvh-2rem)] overflow-y-auto p-5 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div><p class="text-xs font-bold uppercase tracking-[0.16em] text-lime-700">Attendance history</p><h2 class="mt-1 text-2xl font-extrabold">Select a date</h2><p class="mt-1 text-sm text-zinc-500">Choose a date, then apply it to the main attendance list.</p></div>
                    <button type="button" class="rush-secondary !size-11 !px-0" data-attendance-calendar-close aria-label="Close attendance calendar">✕</button>
                </div>

                <form method="GET" action="{{ route('admin.dashboard') }}" data-attendance-calendar-form>
                    <input type="hidden" name="attendance_date" value="{{ $defaultCalendarDate->toDateString() }}" data-attendance-date-input>
                    <div class="mt-6">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-lg font-extrabold">{{ $attendanceMonth->format('F Y') }}</h3>
                            <div class="flex gap-2">
                                <a href="{{ route('admin.dashboard', ['attendance_month' => $attendanceMonth->subMonth()->format('Y-m'), 'attendance_calendar' => 1]) }}" class="rush-secondary !min-h-10 !px-3" aria-label="Previous month">←</a>
                                <a href="{{ route('admin.dashboard', ['attendance_month' => $attendanceMonth->addMonth()->format('Y-m'), 'attendance_calendar' => 1]) }}" class="rush-secondary !min-h-10 !px-3" aria-label="Next month">→</a>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-7 gap-1 text-center text-[0.65rem] font-bold uppercase tracking-wider text-zinc-400">
                            @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $weekday)<span class="py-2">{{ $weekday }}</span>@endforeach
                        </div>
                        <div class="grid grid-cols-7 gap-1" data-attendance-calendar>
                            @foreach ($calendarDays as $calendarDay)
                                @if ($calendarDay['isCurrentMonth'])
                                    @php($isSelectedDate = $calendarDay['date']->isSameDay($defaultCalendarDate))
                                    <button type="button" data-attendance-date="{{ $calendarDay['date']->toDateString() }}" aria-pressed="{{ $isSelectedDate ? 'true' : 'false' }}" class="min-h-16 rounded-lg border p-1.5 text-left transition hover:-translate-y-0.5 hover:border-lime-400 hover:shadow-sm {{ $isSelectedDate ? 'border-lime-400 bg-lime-50 ring-2 ring-lime-100' : 'border-zinc-100 bg-zinc-50' }} {{ $calendarDay['date']->isToday() ? 'font-black' : '' }}">
                                        <span class="flex items-center justify-between gap-1"><time datetime="{{ $calendarDay['date']->toDateString() }}" class="text-xs font-bold">{{ $calendarDay['date']->day }}</time>@if($calendarDay['attendances']->isNotEmpty())<span class="flex min-w-5 items-center justify-center rounded-full bg-lime-400 px-1 text-[0.65rem] font-black text-zinc-950">{{ $calendarDay['attendances']->count() }}</span>@endif</span>
                                    </button>
                                @else
                                    <div class="min-h-16 rounded-lg bg-zinc-50/40 p-1.5 text-zinc-300"><time datetime="{{ $calendarDay['date']->toDateString() }}" class="text-xs font-bold">{{ $calendarDay['date']->day }}</time></div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3 border-t border-zinc-200 pt-5">
                        <button type="button" class="rush-secondary" data-attendance-calendar-close>Cancel</button>
                        <button type="submit" class="rush-primary" data-attendance-calendar-apply>Apply</button>
                    </div>
                </form>
            </div>
        </dialog>
    </section>

    <section class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex items-center justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-600">Next 3 days</p><h2 class="mt-1 text-xl font-extrabold">Expiring subscriptions</h2></div><span class="rounded-full bg-amber-100 px-3 py-1 text-sm font-bold text-amber-800">{{ $expiringSubscriptions->count() }}</span></div>
        <div class="mt-5 grid gap-3">
            @forelse ($expiringSubscriptions as $subscription)
                <div class="flex items-center justify-between gap-4 rounded-lg border border-zinc-200 p-4">
                    <div><p class="font-bold">{{ $subscription->member->full_name }}</p><p class="mt-1 text-sm text-zinc-500">{{ $subscription->plan->name }} · {{ $subscription->member->coach?->name ?? 'No coach' }}</p></div>
                    <div class="text-right"><p class="text-sm font-bold text-amber-700">{{ $subscription->expires_at->diffForHumans() }}</p><p class="text-xs text-zinc-400">{{ $subscription->expires_at->format('M j, Y') }}</p></div>
                </div>
            @empty
                <div class="rounded-xl bg-zinc-50 p-6 text-center text-sm text-zinc-500">No subscriptions expire in the next three days.</div>
            @endforelse
        </div>
    </section>

</div>

@endsection
