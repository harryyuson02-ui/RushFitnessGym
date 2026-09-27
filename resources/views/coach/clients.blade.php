@extends('layouts.layout')

@section('title', 'Coach Clients')

@section('sidebar')
    @include('coach.sidebar')
@endsection

@section('content')

<div class="page-header">
    <h1>Clients</h1>
    <p>Manage and view your assigned gym clients.</p>
</div>

<div class="content-card">

    <div class="card-header">
        <h2>My Clients</h2>

        <button class="primary-btn">
            Add Client
        </button>
    </div>

    <table class="data-table">

        <thead>
            <tr>
                <th>Client</th>
                <th>Membership</th>
                <th>Start Date</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>

            <tr>
                <td>John Doe</td>
                <td>Monthly</td>
                <td>September 1, 2026</td>
                <td>Active</td>
            </tr>

            <tr>
                <td>Jane Smith</td>
                <td>Annual</td>
                <td>January 1, 2026</td>
                <td>Active</td>
            </tr>

            <tr>
                <td>Mike Santos</td>    
                <td>Monthly</td>++
                <td>Active</td>
            </tr>

        </tbody>

    </table>

</div>

@endsection