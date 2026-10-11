<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoachRecordController extends Controller
{
    public function index(Request $request): View
    {
        $member = $request->user()->member;
        $records = $member
            ? $member->coachRecords()
                ->with('coach:id,name,specialty')
                ->latest('recorded_at')
                ->latest('id')
                ->paginate(12)
            : null;

        return view('member.records', compact('records'));
    }
}
