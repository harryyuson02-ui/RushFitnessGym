@extends('layouts.layout')

@section('title', 'Coach Dashboard')

@section('sidebar')
    @include('coach.sidebar')
@endsection

@section('content')

<div class="page-header">
    <h1>Coach Dashboard</h1>
    <p>Welcome to your Rush Fitness Gym coach dashboard.</p>
</div>

<div class="stats-grid">

    <div class="stat-card">
        <h3>My Clients</h3>
        <p>25</p>
    </div>

    <div class="stat-card">
        <h3>Today's Attendance</h3>
        <p>18</p>
    </div>

    <div class="stat-card">
        <h3>Active Clients</h3>
        <p>20</p>
    </div>

    <div class="stat-card">
        <h3>Sessions Today</h3>
        <p>6</p>
    </div>

</div>

<div class="content-card">

    <div class="card-header">
        <h2>Recent Client Activity</h2>
    </div>

    <table class="data-table">

        <thead>
            <tr>
                <th>Client</th>
                <th>Activity</th>
                <th>Date</th>
            </tr>
        </thead>

        <tbody>

            <tr>
                <td>John Doe</td>
                <td>Workout Session</td>
                <td>September 22, 2026</td>
            </tr>

            <tr>
                <td>Jane Smith</td>
                <td>Checked In</td>
                <td>September 22, 2026</td>
            </tr>

            <tr>
                <td>Mike Santos</td>
                <td>Training Session</td>
                <td>September 21, 2026</td>
            </tr>

        </tbody>

    </table>

</div>

@endsection