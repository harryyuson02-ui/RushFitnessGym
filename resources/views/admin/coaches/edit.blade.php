@extends('layouts.admin')
@section('title', 'Edit Coach')
@section('content')
<x-page-heading eyebrow="Coach roster" title="Edit {{ $coach->name }}" description="Keep availability, specialty, and schedule current." />
<form method="POST" action="{{ route('admin.coaches.update', $coach) }}" class="mt-7 rounded-xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-7">@csrf @method('PUT') @include('admin.coaches.form')</form>
@endsection
