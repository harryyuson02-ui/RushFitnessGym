@extends('layouts.portal')

@section('title', 'Edit Member')

@section('content')
<x-page-heading eyebrow="Member profile" title="Edit {{ $member->full_name }}" description="Update contact details and coach assignment." />
<form method="POST" action="{{ route('admin.members.update', $member) }}" class="mt-7 rounded-xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-7">
    @csrf @method('PUT')
    <div class="grid gap-5 sm:grid-cols-2">
        @foreach ([['first_name','First name','text'],['last_name','Last name','text'],['birth_date','Birth date','date'],['gender','Gender','text'],['phone','Mobile number','tel'],['email','Email','email']] as [$name,$label,$type])
            @php($fieldValue = $name === 'birth_date' ? $member->birth_date?->format('Y-m-d') : $member->{$name})
            <label class="grid gap-2 text-sm font-bold">{{ $label }}<input class="min-h-12 rounded-xl border border-zinc-300 px-4 font-normal" type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $fieldValue) }}" {{ in_array($name, ['first_name','last_name','phone']) ? 'required' : '' }}><x-input-error :messages="$errors->get($name)" /></label>
        @endforeach
        <label class="grid gap-2 text-sm font-bold sm:col-span-2">Address<textarea class="min-h-24 rounded-xl border border-zinc-300 p-4 font-normal" name="address">{{ old('address', $member->address) }}</textarea></label>
        <label class="grid gap-2 text-sm font-bold">Message channel<select class="min-h-12 rounded-xl border border-zinc-300 bg-white px-4 font-normal" name="preferred_channel"><option value="private_message" @selected(old('preferred_channel', $member->preferred_channel) === 'private_message')>Private message</option><option value="sms" @selected(old('preferred_channel', $member->preferred_channel) === 'sms')>SMS</option></select></label>
        <label class="grid gap-2 text-sm font-bold">Coach<select class="min-h-12 rounded-xl border border-zinc-300 bg-white px-4 font-normal" name="coach_id">@foreach($coaches as $coach)<option value="{{ $coach->id }}" @selected((string) old('coach_id', $member->coach_id) === (string) $coach->id)>{{ $coach->name }} · {{ $coach->specialty }}</option>@endforeach</select><x-input-error :messages="$errors->get('coach_id')" /></label>
    </div>
    <div class="mt-7 flex flex-wrap justify-end gap-3"><a href="{{ route('admin.members.show', $member) }}" class="inline-flex min-h-12 items-center px-5 font-bold text-zinc-600">Cancel</a><button class="min-h-12 rounded-xl bg-lime-400 px-6 font-extrabold">Save profile</button></div>
</form>
@endsection
