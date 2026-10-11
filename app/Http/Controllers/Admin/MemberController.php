<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Coach;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'member_search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:all,pending,active,expiring,inactive'],
            'user_search' => ['nullable', 'string', 'max:100'],
            'user_role' => ['nullable', 'in:all,admin,coach,user'],
        ]);
        $memberSearch = trim($validated['member_search'] ?? '');
        $status = $validated['status'] ?? 'all';
        $userSearch = trim($validated['user_search'] ?? '');
        $userRole = $validated['user_role'] ?? 'all';
        $totalMembers = Member::count();
        $totalUsers = User::count();
        $members = Member::query()
            ->with(['coach', 'currentSubscription.plan'])
            ->when($memberSearch !== '', function (Builder $query) use ($memberSearch): void {
                foreach (preg_split('/\s+/', $memberSearch) as $term) {
                    $query->where(function (Builder $names) use ($term): void {
                        $names->where('first_name', 'like', '%'.$term.'%')
                            ->orWhere('last_name', 'like', '%'.$term.'%')
                            ->orWhere('member_code', 'like', '%'.$term.'%');
                    });
                }
            })
            ->when(in_array($status, ['active', 'expiring'], true), function (Builder $query) use ($status): void {
                $query->whereHas('currentSubscription', function (Builder $subscription) use ($status): void {
                    $subscription->active();
                    if ($status === 'expiring') {
                        $subscription->whereDate('expires_at', '<=', today()->addDays(3));
                    }
                });
            })
            ->when($status === 'pending', function (Builder $query): void {
                $query->whereHas('currentSubscription', fn (Builder $subscription): Builder => $subscription->where('status', 'pending'));
            })
            ->when($status === 'inactive', function (Builder $query): void {
                $query->whereDoesntHave('currentSubscription', function (Builder $subscription): void {
                    $subscription->where(function (Builder $status): void {
                        $status->active()->orWhere('status', 'pending');
                    });
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(12, ['*'], 'members_page')
            ->withQueryString();
        $users = User::query()
            ->with(['coach:id,user_id,specialty', 'member:id,user_id,member_code'])
            ->when($userSearch !== '', fn (Builder $query): Builder => $query->where(function (Builder $matches) use ($userSearch): void {
                $matches->where('name', 'like', '%'.$userSearch.'%')
                    ->orWhere('email', 'like', '%'.$userSearch.'%');
            }))
            ->when($userRole !== 'all', fn (Builder $query): Builder => $query->where('role', $userRole))
            ->orderBy('name')
            ->get();

        return view('admin.members.index', compact(
            'members',
            'memberSearch',
            'status',
            'totalMembers',
            'users',
            'userSearch',
            'userRole',
            'totalUsers',
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.members.create', [
            'coaches' => Coach::where('is_available', true)->orderBy('name')->get(),
            'plans' => MembershipPlan::where('is_active', true)->orderBy('price')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMemberRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $member = DB::transaction(function () use ($validated): Member {
            $member = Member::create([
                ...collect($validated)->only([
                    'first_name', 'last_name', 'birth_date', 'gender', 'phone', 'email',
                    'address', 'coach_id',
                ])->all(),
                'preferred_channel' => 'private_message',
                'member_code' => $this->nextMemberCode(),
            ]);

            $subscription = $member->subscriptions()->create([
                'membership_plan_id' => $validated['membership_plan_id'],
                'starts_at' => $validated['starts_at'],
                'expires_at' => $validated['expires_at'],
                'status' => 'active',
                'access_token' => (string) Str::uuid(),
                'paid_at' => now(),
                'qr_issued_at' => now(),
            ]);

            $plan = MembershipPlan::findOrFail($validated['membership_plan_id']);
            $member->transactions()->create([
                'subscription_id' => $subscription->id,
                'membership_plan_id' => $plan->id,
                'amount' => $plan->price,
                'payment_method' => 'cash',
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            return $member;
        });

        return redirect()->route('admin.members.show', $member)
            ->with('success', 'Enrollment confirmed. The QR access key is ready to send.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Member $member): View
    {
        $member->load(['coach', 'currentSubscription.plan', 'subscriptions.plan']);
        $subscription = $member->currentSubscription;
        $message = $subscription
            ? "Welcome to Rush Fitness Gym, {$member->first_name}! Your {$subscription->plan->name} access QR is ready: ".route('access-key.image', $subscription->access_token)
            : null;

        return view('admin.members.show', [
            'member' => $member,
            'subscription' => $subscription,
            'plans' => MembershipPlan::where('is_active', true)->orderBy('price')->get(),
            'messageUrl' => $message === null ? null : ($member->user
                ? route('chat.show', $member->user)
                : 'sms:'.$member->phone.'?body='.rawurlencode($message)),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Member $member): View
    {
        return view('admin.members.edit', [
            'member' => $member,
            'coaches' => Coach::where('is_available', true)->orWhereKey($member->coach_id)->orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMemberRequest $request, Member $member): RedirectResponse
    {
        $member->update($request->validated());

        return redirect()->route('admin.members.show', $member)->with('success', 'Member profile updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Member $member): RedirectResponse
    {
        $member->delete();

        return redirect()->route('admin.members.index')->with('success', 'Member and subscription history deleted.');
    }

    private function nextMemberCode(): string
    {
        do {
            $code = 'RFG-'.now()->format('ym').'-'.Str::upper(Str::random(6));
        } while (Member::where('member_code', $code)->exists());

        return $code;
    }
}
