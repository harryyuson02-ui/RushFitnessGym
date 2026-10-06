<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function index(Request $request): View
    {
        $coach = $request->user()->coach;
        abort_unless($coach, 403);

        $memberApplications = Subscription::query()
            ->with([
                'member:id,user_id,coach_id,member_code,first_name,last_name,email,phone,fitness_goal,availability_days,availability_starts_at,availability_ends_at',
                'plan:id,name,price',
                'transactions:id,subscription_id,payment_method,reference,receipt_path,status',
            ])
            ->where('status', 'pending')
            ->whereHas('member', fn (Builder $query): Builder => $query->whereBelongsTo($coach))
            ->latest()
            ->get();

        return view('coach.approvals.index', compact('memberApplications'));
    }
}
