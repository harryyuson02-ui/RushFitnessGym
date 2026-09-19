
@extends('layouts.layout')

@section('title', 'Member Dashboard')

@section('header', 'Member Dashboard')

@section('content')

    <div class="welcome-card">

        <h2>Welcome, Member!</h2>

        <p>
            Manage your gym activities and membership.
        </p>

    </div>

    <div class="dashboard-cards">

        <div class="dashboard-card">
            <h3>Membership Status</h3>
            <p>Active</p>
        </div>

        <div class="dashboard-card">
            <h3>Check-ins</h3>
            <p>0</p>
        </div>

        <div class="dashboard-card">
            <h3>QR Code</h3>
            <p>Coming Soon</p>
        </div>

    </div>

@endsection