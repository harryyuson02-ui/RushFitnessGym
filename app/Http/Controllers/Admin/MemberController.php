<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Coach;
use App\Models\Member;
use App\Models\MembershipPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $members = Member::query()
            ->with(['coach', 'currentSubscription.plan'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('admin.members.index', compact('members'));
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
                    'address', 'preferred_channel', 'coach_id',
                ])->all(),
                'member_code' => $this->nextMemberCode(),
            ]);

            $member->subscriptions()->create([
                'membership_plan_id' => $validated['membership_plan_id'],
                'starts_at' => $validated['starts_at'],
                'expires_at' => $validated['expires_at'],
                'status' => 'active',
                'access_token' => (string) Str::uuid(),
                'paid_at' => now(),
                'qr_issued_at' => now(),
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
        $phone = preg_replace('/\D+/', '', $member->phone) ?? '';

        return view('admin.members.show', [
            'member' => $member,
            'subscription' => $subscription,
            'plans' => MembershipPlan::where('is_active', true)->orderBy('price')->get(),
            'messageUrl' => $message === null ? null : ($member->preferred_channel === 'whatsapp'
                ? 'https://wa.me/'.$phone.'?text='.rawurlencode($message)
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
