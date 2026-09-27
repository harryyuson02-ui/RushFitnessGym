@extends('layouts.admin')
@section('title', 'Add Plan')
@section('content')
<x-page-heading eyebrow="Subscription catalog" title="Add plan" description="Create a tier available at onboarding and renewal." /><form method="POST" action="{{ route('admin.plans.store') }}" class="mt-7 rounded-xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-7">@csrf @include('admin.plans.form', ['plan' => null])</form>
@endsection
