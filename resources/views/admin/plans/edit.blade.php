@extends('layouts.portal')
@section('title', 'Edit Plan')
@section('content')
<x-page-heading eyebrow="Subscription catalog" title="Edit {{ $plan->name }}" description="Changes apply to future onboarding and renewal selections." /><form method="POST" action="{{ route('admin.plans.update', $plan) }}" class="mt-7 rounded-xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-7">@csrf @method('PUT') @include('admin.plans.form')</form>
@endsection
