@extends('layouts.portal')
@section('title', 'Change Password')
@section('content')
<x-page-heading eyebrow="Account security" title="Change password" description="Enter your current password before choosing a new one." />
<form method="POST" action="{{ route('profile.update') }}" class="rush-card mt-6 max-w-2xl p-5 sm:p-7">
    @csrf
    @method('PUT')
    <div class="grid gap-5">
        <label class="rush-label">Current password<input class="rush-input" type="password" name="current_password" autocomplete="current-password" required></label>
        <label class="rush-label">New password<input class="rush-input" type="password" name="password" autocomplete="new-password" required></label>
        <label class="rush-label">Confirm new password<input class="rush-input" type="password" name="password_confirmation" autocomplete="new-password" required></label>
    </div>
    <x-input-error :messages="$errors->all()" />
    <div class="mt-6 flex justify-end"><button class="rush-primary" type="submit">Change password</button></div>
</form>
@endsection
