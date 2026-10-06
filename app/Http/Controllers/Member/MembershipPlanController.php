<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\MembershipPlan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MembershipPlanController extends Controller
{
    public function index(Request $request): View
    {
        $currentSubscription = $request->user()->member
            ?->currentSubscription()
            ->with('plan:id,name')
            ->first();

        return view('member.plans', [
            'currentSubscription' => $currentSubscription,
            'plans' => MembershipPlan::query()
                ->where('is_active', true)
                ->orderBy('price')
                ->orderBy('id')
                ->get(),
        ]);
    }
}
