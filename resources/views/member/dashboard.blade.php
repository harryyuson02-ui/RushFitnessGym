@extends('layouts.layout')

@section('title', 'Member Dashboard')

@section('sidebar')
    @include('member.sidebar')
@endsection

@section('content')
<div class="page-header">
    <h1>Member Dashboard</h1>
    <p>Welcome to your Rush Fitness Gym member dashboard.</p>
</div>

<!-- 4 Statistics Cards Row -->
<div class="stats-grid">
    <div class="stat-card">
        <h3>Membership Status</h3>
        <p style="color: #24f404;">Active</p>
    </div>

    <div class="stat-card">
        <h3>Total Visits</h3>
        <p>18</p>
    </div>

    <div class="stat-card">
        <h3>Days Remaining</h3>
        <p>28</p>
    </div>

    <div class="stat-card">
        <h3>Workouts Completed</h3>
        <p>12</p>
    </div>
</div>

<!-- Recent Activity Section -->
<div class="content-card">
    <h2>Recent Activity</h2>

    <table class="data-table">
        <thead>
            <tr>
                <th>Activity</th>
                <th>Location / Note</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Gym Check-in</td>
                <td>Main Entrance Terminal</td>
                <td>September 26, 2026</td>
            </tr>
            <tr>
                <td>Gym Check-in</td>
                <td>Main Entrance Terminal</td>
                <td>September 24, 2026</td>
            </tr>
            <tr>
                <td>Personal Training</td>
                <td>Assigned with Coach Mark</td>
                <td>September 22, 2026</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection