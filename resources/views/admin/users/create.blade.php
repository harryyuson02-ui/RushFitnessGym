@extends('layouts.portal')
@section('title', 'Add Account')
@section('content')
<x-page-heading eyebrow="Portal access" title="Add account" description="Create an administrator, coach, or user login and optionally link an existing record." />
<form method="POST" action="{{ route('admin.users.store') }}" class="rush-card mt-6 p-5 sm:p-7">@csrf @include('admin.users.form', ['user' => null])</form>
@endsection
