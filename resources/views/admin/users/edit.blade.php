@extends('layouts.portal')
@section('title', 'Edit Account')
@section('content')
<x-page-heading eyebrow="Portal access" title="Edit {{ $user->name }}" description="Update login details, role, contact channel, and linked roster record." />
<form method="POST" action="{{ route('admin.users.update', $user) }}" class="rush-card mt-6 p-5 sm:p-7">@csrf @method('PUT') @include('admin.users.form')</form>
@endsection
