@extends('layouts.layout')

@section('title', 'Attendance')

@section('sidebar')
    @include('member.sidebar')
@endsection

@section('content')
<div class="dashboard-container">
    <h1>Attendance</h1>
    <p class="subtitle">View your gym visit logs and attendance history.</p>

    <!-- 4 Stat Cards Row -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-title">This Month</span>
            <div class="stat-number">14</div>
        </div>

        <div class="stat-card">
            <span class="stat-title">This Week</span>
            <div class="stat-number">4</div>
        </div>

        <div class="stat-card">
            <span class="stat-title">Last Visit</span>
            <div class="stat-number" style="font-size: 20px;">Sept 26</div>
        </div>

        <div class="stat-card">
            <span class="stat-title">Streak</span>
            <div class="stat-number">3 Days</div>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="table-card">
        <h2>Attendance Logs</h2>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time In</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>September 26, 2026</td>
                    <td>08:30 AM</td>
                    <td><span class="status-active">Checked In</span></td>
                </tr>
                <tr>
                    <td>September 24, 2026</td>
                    <td>05:15 PM</td>
                    <td><span class="status-active">Checked In</span></td>
                </tr>
                <tr>
                    <td>September 22, 2026</td>
                    <td>07:00 AM</td>
                    <td><span class="status-active">Checked In</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection