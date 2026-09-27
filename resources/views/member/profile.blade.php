@extends('layouts.layout')

@section('title', 'Profile')

@section('sidebar')
    @include('member.sidebar')
@endsection

@section('content')
<div class="page-header">
    <h1>Member Profile</h1>
    <p>View and manage your account details and check-in history.</p>
</div>

<!-- Profile Header Card -->
<div class="content-card">
    <div style="display: flex; gap: 20px; align-items: center; margin-bottom: 20px;">
        <div style="width: 60px; height: 60px; border-radius: 50%; background: #3ad222; color: #1e1e1e; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700;">
            {{ strtoupper(substr(optional(auth()->user())->name ?? 'M', 0, 1)) }}
        </div>
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <h2 style="margin: 0; font-size: 22px;">{{ optional(auth()->user())->name ?? 'Member Name' }}</h2>
                <span style="background: #eee; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">#00{{ optional(auth()->user())->id ?? '1' }}</span>
            </div>
            <div style="color: #3ad222; font-weight: 600; font-size: 14px; margin-top: 2px;">VIP Tier Member</div>
            <div style="color: #777; font-size: 13px; margin-top: 2px;">
                Joined {{ auth()->check() && auth()->user()->created_at ? auth()->user()->created_at->format('M Y') : 'Oct 2023' }} • 1 yr member
            </div>
        </div>
    </div>

    <!-- Status Pills -->
    <div style="display: flex; gap: 10px; margin-bottom: 20px;">
        <span style="background: #e6f9e6; color: #1e7e1e; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
            ● Status: Active
        </span>
        <span style="background: #f0f0f0; color: #555; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
            ✓ Turnstile Synced
        </span>
        <span style="background: #f0f0f0; color: #555; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
            Locker #42
        </span>
    </div>

    <!-- Action Buttons -->
    <div style="display: flex; gap: 10px;">
        <a href="mailto:{{ optional(auth()->user())->email ?? '' }}" class="primary-btn" style="text-decoration: none; display: inline-block;">
            ✉️ Contact Email
        </a>
    </div>
</div>

<!-- Membership Details -->
<div class="content-card">
    <div class="card-header">
        <h2>Membership Details</h2>
        <span style="background: #e6f9e6; color: #1e7e1e; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600;">Plan Active</span>
    </div>

    <table class="data-table">
        <tbody>
            <tr>
                <td style="font-weight: 600; color: #777; width: 200px;">Current Plan</td>
                <td>VIP Annual Full Access ($625/yr)</td>
            </tr>
            <tr>
                <td style="font-weight: 600; color: #777;">Term Dates</td>
                <td>
                    Oct 24, 2024 – Oct 24, 2025
                    <div style="color: #3ad222; font-size: 12px; font-weight: 600; margin-top: 2px;">365 days remaining</div>
                </td>
            </tr>
            <tr>
                <td style="font-weight: 600; color: #777;">Auto-Renew</td>
                <td style="color: #1e7e1e; font-weight: 600;">
                    ● Enabled (Visa •••• 4242)
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Attendance & Check-in -->
<div class="content-card">
    <div class="card-header">
        <h2>Attendance & Check-in</h2>
        <span style="color: #777; font-size: 13px; font-weight: 600;">Weekly Avg: 3.5x</span>
    </div>

    <div style="margin-bottom: 20px; padding: 15px; background: #fafafa; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <div style="font-size: 20px; font-weight: 700;">42 Visits</div>
            <div style="font-size: 12px; color: #777;">Current calendar year</div>
        </div>
        <span style="background: #eee; padding: 4px 10px; border-radius: 6px; font-size: 12px;">Regular Member</span>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Status</th>
                <th>Check-in Details</th>
                <th>Time & Date</th>
                <th>Access</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="color: #3ad222; font-weight: bold;">✓</td>
                <td>Front Turnstile A • Pro Pass</td>
                <td>Today, 11:44 AM</td>
                <td><span style="background: #e6f9e6; color: #1e7e1e; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">GRANTED</span></td>
            </tr>
            <tr>
                <td style="color: #3ad222; font-weight: bold;">✓</td>
                <td>Front Turnstile B • QR Scan</td>
                <td>Yesterday, 6:15 PM</td>
                <td><span style="background: #e6f9e6; color: #1e7e1e; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">GRANTED</span></td>
            </tr>
            <tr>
                <td style="color: #3ad222; font-weight: bold;">✓</td>
                <td>Front Turnstile A • QR Scan</td>
                <td>Oct 21, 2024 - 8:02 AM</td>
                <td><span style="background: #e6f9e6; color: #1e7e1e; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">GRANTED</span></td>
            </tr>
        </tbody>
    </table>
</div>
@endsection