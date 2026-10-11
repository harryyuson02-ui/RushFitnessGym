<div class="grid gap-5 sm:grid-cols-2">
    <label class="rush-label">Full name<input class="rush-input" name="name" value="{{ old('name', $user?->name) }}" required></label>
    <label class="rush-label">Email<input class="rush-input" type="email" name="email" value="{{ old('email', $user?->email) }}" required></label>
    <label class="rush-label">Password<input class="rush-input" type="password" name="password" @required(!$user) autocomplete="new-password"><span class="text-xs font-normal text-zinc-500">{{ $user ? 'Leave blank to keep the current password.' : 'Minimum 8 characters.' }}</span></label>
    <label class="rush-label">Confirm password<input class="rush-input" type="password" name="password_confirmation" @required(!$user) autocomplete="new-password"></label>
    <label class="rush-label">Role<select class="rush-input" name="role" required>@foreach(['admin' => 'Administrator', 'coach' => 'Coach', 'user' => 'User / member'] as $value => $label)<option value="{{ $value }}" @selected(old('role', $user?->role ?? 'user') === $value)>{{ $label }}</option>@endforeach</select></label>
    <label class="rush-label">Phone<input class="rush-input" name="phone" value="{{ old('phone', $user?->phone) }}"></label>
    <label class="rush-label">Coach roster link<select class="rush-input" name="coach_id"><option value="">No coach link</option>@foreach($coaches as $coach)<option value="{{ $coach->id }}" @selected((string) old('coach_id', $user?->coach?->id) === (string) $coach->id)>{{ $coach->name }} · {{ $coach->specialty }}</option>@endforeach</select></label>
    <label class="rush-label">Member record link<select class="rush-input" name="member_id"><option value="">No member link (user can apply)</option>@foreach($members as $member)<option value="{{ $member->id }}" @selected((string) old('member_id', $user?->member?->id) === (string) $member->id)>{{ $member->full_name }} · {{ $member->member_code }}</option>@endforeach</select></label>
</div>
<x-input-error :messages="$errors->all()" />
<div class="mt-7 flex justify-end gap-3"><a href="{{ route('admin.members.index').'#system-users' }}" class="rush-secondary">Cancel</a><button class="rush-primary" type="submit">Save account</button></div>
