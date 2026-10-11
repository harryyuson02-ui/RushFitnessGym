<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCoachRequest;
use App\Http\Requests\UpdateCoachRequest;
use App\Models\Coach;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CoachController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'specialty' => ['nullable', Rule::in(Coach::SPECIALTIES)],
        ]);
        $search = trim($validated['search'] ?? '');
        $specialty = $validated['specialty'] ?? '';
        $coaches = Coach::withCount('members')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $matches) use ($search): void {
                    $matches->where('name', 'like', '%'.$search.'%')
                        ->orWhere('specialty', 'like', '%'.$search.'%');
                });
            })
            ->when($specialty !== '', fn (Builder $query): Builder => $query->where('specialty', $specialty))
            ->orderBy('name')->paginate(12)->withQueryString();

        return view('admin.coaches.index', compact('coaches', 'search', 'specialty'));
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
        $validated = $request->validated();
        $availabilitySlots = $this->availabilitySlots($validated['availability']);

        DB::transaction(function () use ($validated, $availabilitySlots, $request): void {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => 'coach',
                'phone' => $validated['phone'],
            ]);
            $coach = Coach::create([
                ...Arr::except($validated, ['availability', 'password', 'password_confirmation']),
                'user_id' => $user->id,
                'schedule' => Coach::scheduleSummary($availabilitySlots),
                'is_available' => $request->boolean('is_available'),
            ]);

            $coach->availabilitySlots()->createMany($availabilitySlots);
        });

        return redirect()->route('admin.coaches.index')
            ->with('success', 'Coach added to the roster with a portal account.');
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
        return view('admin.coaches.edit', ['coach' => $coach->load('availabilitySlots')]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCoachRequest $request, Coach $coach): RedirectResponse
    {
        $validated = $request->validated();
        $availabilitySlots = $this->availabilitySlots($validated['availability']);

        DB::transaction(function () use ($coach, $validated, $availabilitySlots, $request): void {
            $coach->update([
                ...Arr::except($validated, ['availability']),
                'schedule' => Coach::scheduleSummary($availabilitySlots),
                'is_available' => $request->boolean('is_available'),
            ]);

            $coach->availabilitySlots()->delete();
            $coach->availabilitySlots()->createMany($availabilitySlots);
        });

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

    /**
     * @param  array<int, array{day_of_week: int|string, enabled?: bool|string, starts_at: string, ends_at: string}>  $availability
     * @return array<int, array{day_of_week: int, starts_at: string, ends_at: string, is_available: bool}>
     */
    private function availabilitySlots(array $availability): array
    {
        return collect($availability)
            ->filter(fn (array $slot): bool => (bool) ($slot['enabled'] ?? false))
            ->map(fn (array $slot): array => [
                'day_of_week' => (int) $slot['day_of_week'],
                'starts_at' => $slot['starts_at'],
                'ends_at' => $slot['ends_at'],
                'is_available' => true,
            ])
            ->sortBy('day_of_week')
            ->values()
            ->all();
    }
}
