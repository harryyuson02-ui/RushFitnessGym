<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCoachClientRecordRequest;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;

class ClientRecordController extends Controller
{
    public function store(StoreCoachClientRecordRequest $request, Member $member): RedirectResponse
    {
        $coach = $request->user()->coach;
        abort_unless($coach && Member::query()->activeClientOf($coach)->whereKey($member->getKey())->exists(), 404);
        $coach->clientRecords()->create([...$request->validated(), 'member_id' => $member->id]);

        return back()->with('success', 'Client record added.');
    }
}
