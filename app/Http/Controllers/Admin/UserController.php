<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserAccountRequest;
use App\Http\Requests\UpdateUserAccountRequest;
use App\Models\Coach;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'in:all,admin,coach,user'],
        ]);
        $search = trim($validated['search'] ?? '');
        $role = $validated['role'] ?? 'all';
        $users = User::query()
            ->with(['coach:id,user_id,specialty', 'member:id,user_id,member_code'])
            ->when($search !== '', fn (Builder $query): Builder => $query->where(function (Builder $matches) use ($search): void {
                $matches->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%');
            }))
            ->when($role !== 'all', fn (Builder $query): Builder => $query->where('role', $role))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search', 'role'));
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'coaches' => Coach::whereNull('user_id')->orderBy('name')->get(),
            'members' => Member::whereNull('user_id')->orderBy('last_name')->get(),
        ]);
    }

    public function store(StoreUserAccountRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated): User {
            $user = User::create(collect($validated)->only(['name', 'email', 'password', 'role', 'phone', 'preferred_channel'])->all());
            $this->linkAccount($user, $validated);

            return $user;
        });

        return redirect()->route('admin.users.edit', $user)->with('success', 'Portal account created.');
    }

    public function edit(User $user): View
    {
        $user->load(['coach', 'member']);

        return view('admin.users.edit', [
            'user' => $user,
            'coaches' => Coach::whereNull('user_id')->orWhere('user_id', $user->id)->orderBy('name')->get(),
            'members' => Member::whereNull('user_id')->orWhere('user_id', $user->id)->orderBy('last_name')->get(),
        ]);
    }

    public function update(UpdateUserAccountRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($user, $validated): void {
            $attributes = collect($validated)->only(['name', 'email', 'role', 'phone', 'preferred_channel'])->all();
            if (! empty($validated['password'])) {
                $attributes['password'] = $validated['password'];
            }
            $user->update($attributes);
            $this->linkAccount($user, $validated);
        });

        return back()->with('success', 'Portal account updated.');
    }

    /** @param array<string, mixed> $validated */
    private function linkAccount(User $user, array $validated): void
    {
        $coach = ! empty($validated['coach_id']) ? Coach::findOrFail($validated['coach_id']) : null;
        $member = ! empty($validated['member_id']) ? Member::findOrFail($validated['member_id']) : null;

        if ($coach?->user_id && $coach->user_id !== $user->id) {
            throw ValidationException::withMessages(['coach_id' => 'That coach is already linked to another account.']);
        }
        if ($member?->user_id && $member->user_id !== $user->id) {
            throw ValidationException::withMessages(['member_id' => 'That member is already linked to another account.']);
        }

        Coach::where('user_id', $user->id)->update(['user_id' => null]);
        Member::where('user_id', $user->id)->update(['user_id' => null]);

        if ($user->isCoach()) {
            $coach?->update(['user_id' => $user->id]);
        }
        if ($user->isMember()) {
            $member?->update(['user_id' => $user->id]);
        }
    }
}
