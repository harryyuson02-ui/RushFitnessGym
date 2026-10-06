<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $member = $request->user()->member;
        $transactions = $member
            ? $member->transactions()->with('plan:id,name')->latest()->paginate(15)
            : null;

        return view('member.transactions', compact('transactions'));
    }
}
