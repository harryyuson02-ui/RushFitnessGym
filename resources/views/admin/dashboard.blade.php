
@extends('layouts.layout')

@section('title', 'Admin Dashboard')

@section('header', 'Admin Dashboard')

@section('content')

    <div class="welcome-card">

        <h2>Welcome to Rush Fitness Gym!</h2>

        <p>
            Manage members, attendance, and gym operations.
        </p>

    </div>

    <div class="dashboard-cards">

        <div class="dashboard-card">
            <h3>Total Members</h3>
            <p>200+</p>
        </div>

        <div class="dashboard-card">
            <h3>Attendance</h3>
            <p>Coming Soon</p>
        </div>

        <div class="dashboard-card">
            <h3>Memberships</h3>
            <p>Coming Soon</p>
        </div>

        <div class="dashboard-card">
            <h3>Reports</h3>
            <p>Coming Soon</p>
        </div>

    </div>

@endsection