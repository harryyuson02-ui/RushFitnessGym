<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMembershipPlanRequest;
use App\Http\Requests\UpdateMembershipPlanRequest;
use App\Models\MembershipPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MembershipPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.plans.index', ['plans' => MembershipPlan::withCount('subscriptions')->orderBy('price')->get()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.plans.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMembershipPlanRequest $request): RedirectResponse
    {
        MembershipPlan::create([...$request->validated(), 'is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.plans.index')->with('success', 'Membership plan created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MembershipPlan $plan): RedirectResponse
    {
        return redirect()->route('admin.plans.edit', $plan);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MembershipPlan $plan): View
    {
        return view('admin.plans.edit', compact('plan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMembershipPlanRequest $request, MembershipPlan $plan): RedirectResponse
    {
        $plan->update([...$request->validated(), 'is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.plans.index')->with('success', 'Membership plan updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MembershipPlan $plan): RedirectResponse
    {
        if ($plan->subscriptions()->exists()) {
            return back()->with('error', 'Plans with subscription history cannot be deleted. Mark the plan inactive instead.');
        }

        $plan->delete();

        return redirect()->route('admin.plans.index')->with('success', 'Membership plan deleted.');
    }
}
