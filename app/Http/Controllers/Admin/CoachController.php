<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCoachRequest;
use App\Http\Requests\UpdateCoachRequest;
use App\Models\Coach;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CoachController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.coaches.index', ['coaches' => Coach::withCount('members')->orderBy('name')->get()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.coaches.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCoachRequest $request): RedirectResponse
    {
        Coach::create([...$request->validated(), 'is_available' => $request->boolean('is_available')]);

        return redirect()->route('admin.coaches.index')->with('success', 'Coach added to the roster.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Coach $coach): RedirectResponse
    {
        return redirect()->route('admin.coaches.edit', $coach);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Coach $coach): View
    {
        return view('admin.coaches.edit', compact('coach'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCoachRequest $request, Coach $coach): RedirectResponse
    {
        $coach->update([...$request->validated(), 'is_available' => $request->boolean('is_available')]);

        return redirect()->route('admin.coaches.index')->with('success', 'Coach details updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Coach $coach): RedirectResponse
    {
        $coach->delete();

        return redirect()->route('admin.coaches.index')->with('success', 'Coach removed from the roster.');
    }
}
