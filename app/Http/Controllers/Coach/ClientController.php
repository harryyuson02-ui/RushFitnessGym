<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $coach = $request->user()->coach;
        abort_unless($coach, 403);
        $clients = Member::query()
            ->activeClientOf($coach)
            ->with(['currentSubscription.plan'])
            ->orderBy('last_name')
            ->paginate(15);

        return view('coach.clients.index', compact('clients'));
    }

    public function show(Request $request, Member $member): View
    {
        $coach = $request->user()->coach;
        abort_unless($coach && Member::query()->activeClientOf($coach)->whereKey($member->getKey())->exists(), 404);
        $member->load(['currentSubscription.plan', 'coachRecords' => fn ($query) => $query->whereBelongsTo($coach)->latest('recorded_at')]);

        return view('coach.clients.show', compact('member'));
    }
}
