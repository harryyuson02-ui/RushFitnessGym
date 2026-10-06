<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use App\Models\Member;
use App\Models\Subscription;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $expiringSubscriptions = Subscription::query()
            ->with(['member.coach', 'plan'])
            ->where('status', 'active')
            ->whereBetween('expires_at', [today(), today()->addDays(3)])
            ->orderBy('expires_at')
            ->get();

        return view('admin.dashboard', [
            'memberCount' => Member::count(),
            'activeSubscriptionCount' => Subscription::active()->count(),
            'availableCoachCount' => Coach::where('is_available', true)->count(),
            'expiringSubscriptions' => $expiringSubscriptions,
        ]);
    }
}
