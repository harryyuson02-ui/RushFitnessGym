@extends('layouts.admin')
@section('title', 'Add Coach')
@section('content')
<x-page-heading eyebrow="Coach roster" title="Add coach" description="Record a specialty and working schedule for member selection." />
<form method="POST" action="{{ route('admin.coaches.store') }}" class="mt-7 rounded-xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-7">@csrf @include('admin.coaches.form', ['coach' => null])</form>
@endsection
