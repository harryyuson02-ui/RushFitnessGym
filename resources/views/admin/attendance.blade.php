@extends('layouts.layout')

@section('title', 'Admin Attendance')

@section('sidebar')
    @include('admin.sidebar')
@endsection

@section('content')

<div class="page-header">
    <h1>Attendance</h1>
    <p>Monitor gym attendance records.</p>
</div>

<div class="content-card">

    <div class="card-header">
        <h2>Attendance Records</h2>
    </div>

    <table class="data-table">

        <thead>
            <tr>
                <th>Member</th>
                <th>Date</th>
                <th>Time In</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>

            <tr>
                <td>John Doe</td>
                <td>September 22, 2026</td>
                <td>8:30 AM</td>
                <td>Present</td>
            </tr>

            <tr>
                <td>Jane Smith</td>
                <td>September 22, 2026</td>
                <td>9:15 AM</td>
                <td>Present</td>
            </tr>

            <tr>
                <td>Mike Santos</td>
                <td>September 21, 2026</td>
                <td>7:45 AM</td>
                <td>Present</td>
            </tr>

        </tbody>

    </table>

</div>

@endsection