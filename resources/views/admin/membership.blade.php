@extends('layouts.layout')

@section('title', 'Admin Membership')

@section('sidebar')
    @include('admin.sidebar')
@endsection

@section('content')

<div class="page-header">
    <h1>Membership</h1>
    <p>Manage gym memberships.</p>
</div>

<div class="content-card">

    <div class="card-header">
        <h2>Membership Records</h2>

        <button class="primary-btn">
            Add Membership
        </button>
    </div>

    <table class="data-table">

        <thead>
            <tr>
                <th>Member</th>
                <th>Membership Type</th>
                <th>Start Date</th>
                <th>Expiration</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>

            <tr>
                <td>John Doe</td>
                <td>Monthly</td>
                <td>September 1, 2026</td>
                <td>September 30, 2026</td>
                <td>Active</td>
            </tr>

            <tr>
                <td>Jane Smith</td>
                <td>Annual</td>
                <td>January 1, 2026</td>
                <td>December 31, 2026</td>
                <td>Active</td>
            </tr>

            <tr>
                <td>Mike Santos</td>
                <td>Monthly</td>
                <td>August 15, 2026</td>
                <td>September 15, 2026</td>
                <td>Expired</td>
            </tr>

        </tbody>

    </table>

</div>

@endsection