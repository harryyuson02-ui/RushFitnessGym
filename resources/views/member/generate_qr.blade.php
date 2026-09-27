@extends('layouts.layout')

@section('title', 'Generate QR Code')

@section('sidebar')
    @include('member.sidebar')
@endsection

@section('content')
<div class="dashboard-container">
    <h1>Generate QR Code</h1>
    <p class="subtitle">Your digital pass for scanner verification at the gym gate.</p>

    <!-- 4 Stat Cards Row -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-title">Pass Code</span>
            <div class="stat-number" style="font-size: 22px;">RUSH-{{ auth()->user()->id ?? '101' }}</div>
        </div>

        <div class="stat-card">
            <span class="stat-title">Gate Status</span>
            <div class="stat-number status-active">Ready</div>
        </div>

        <div class="stat-card">
            <span class="stat-title">Valid Until</span>
            <div class="stat-number" style="font-size: 20px;">Today 9:00 PM</div>
        </div>

        <div class="stat-card">
            <span class="stat-title">Scan Limit</span>
            <div class="stat-number">Unlimited</div>
        </div>
    </div>

    <!-- QR Pass Display Card -->
    <div class="table-card" style="text-align: center; padding: 40px 20px;">
        <h2>Digital Access Pass</h2>
        <p style="color: #666; margin-bottom: 25px;">Scan this at the entrance terminal to verify your check-in.</p>

        <div style="background: #ffffff; border: 2px solid #eeeeee; padding: 25px; display: inline-block; border-radius: 16px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=MEMBER_PASS_{{ auth()->user()->id ?? '101' }}" alt="Member QR Pass">
        </div>

        <div>
            <span class="status-active" style="font-size: 15px; font-weight: 600;">● Active Pass</span>
        </div>
    </div>
</div>
@endsection