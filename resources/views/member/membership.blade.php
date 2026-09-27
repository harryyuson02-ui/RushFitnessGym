@extends('layouts.layout')

@section('title', 'Membership')

@section('sidebar')
    @include('member.sidebar')
@endsection

@section('content')
<div class="dashboard-container">
    <h1>Membership</h1>
    <p class="subtitle">Overview of your current plan and subscription details.</p>

    <!-- 4 Stat Cards Row -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-title">Plan Type</span>
            <div class="stat-number" style="font-size: 24px;">Regular VIP</div>
        </div>

        <div class="stat-card">
            <span class="stat-title">Status</span>
            <div class="stat-number status-active">Active</div>
        </div>

        <div class="stat-card">
            <span class="stat-title">Start Date</span>
            <div class="stat-number" style="font-size: 20px;">Sept 01, 2026</div>
        </div>

        <div class="stat-card">
            <span class="stat-title">Expiration Date</span>
            <div class="stat-number" style="font-size: 20px;">Oct 01, 2026</div>
        </div>
    </div>

    <!-- Plan Overview Card -->
    <div class="table-card">
        <h2>Plan Inclusions</h2>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Feature</th>
                    <th>Access Level</th>
                    <th>Availability</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Gym Access</td>
                    <td>Full Access (7 AM - 9 PM)</td>
                    <td>6 Days a Week</td>
                </tr>
                <tr>
                    <td>Free Weights & Machines</td>
                    <td>Unlimited</td>
                    <td>Always Available</td>
                </tr>
                <tr>
                    <td>Locker & Shower Access</td>
                    <td>Included</td>
                    <td>Standard Access</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Subscription Plans -->
    <h2 class="section-title">Subscription Plans</h2>

    <div class="plans-grid">

        {{-- VIP Annual Full Access --}}
        <div class="plan-card plan-card--featured">
            <span class="plan-card__badge">Most Popular</span>

            <h3 class="plan-card__name">VIP Annual Full Access</h3>
            <p class="plan-card__members">{{ 214 }} Active Members</p>

            <div class="plan-card__price">
                <span class="plan-card__amount">$625</span><span class="plan-card__period">/yr</span>
            </div>
            <p class="plan-card__subprice">approx. $52/mo</p>

            <ul class="plan-card__features">
                <li>24/7 Turnstile Access (QR & Bio)</li>
                <li>Dedicated Smart Locker</li>
                <li>Guest passes (2/mo) + Full Recovery Spa</li>
            </ul>

            <div class="plan-card__footer">
                <span class="plan-card__id">TIER-VIP-ANNUAL</span>
                <a href="#" class="btn-edit-tier btn-edit-tier--featured">Edit Tier</a>
            </div>
        </div>

        {{-- Standard Monthly Plan --}}
        <div class="plan-card">
            <h3 class="plan-card__name">Standard Monthly Plan</h3>
            <p class="plan-card__members">{{ 185 }} Active Members</p>

            <div class="plan-card__price">
                <span class="plan-card__amount">$45</span><span class="plan-card__period">/mo</span>
            </div>
            <p class="plan-card__subprice">Billed monthly</p>

            <ul class="plan-card__features">
                <li>Peak gym access (5:00 AM - 11:00 PM)</li>
                <li>Standard Day Locker</li>
                <li>Auto-renew & Fast QR Check-in</li>
            </ul>

            <div class="plan-card__footer">
                <span class="plan-card__id">TIER-STD-MONTHLY</span>
                <a href="#" class="btn-edit-tier">Edit Tier</a>
            </div>
        </div>

        {{-- Morning / Off-Peak --}}
        <div class="plan-card">
            <h3 class="plan-card__name">Morning / Off-Peak</h3>
            <p class="plan-card__members">{{ 53 }} Active Members</p>

            <div class="plan-card__price">
                <span class="plan-card__amount">$30</span><span class="plan-card__period">/mo</span>
            </div>
            <p class="plan-card__subprice">Off-peak only</p>

            <ul class="plan-card__features">
                <li>Gym access 5:00 AM - 9:00 AM</li>
                <li>Shared locker access</li>
            </ul>

            <div class="plan-card__footer">
                <span class="plan-card__id">TIER-OFFPEAK</span>
                <a href="#" class="btn-edit-tier">Edit Tier</a>
            </div>
        </div>

    </div>
</div>

<style>
    .section-title{
        margin:32px 0 16px;
        font-size:24px;
        font-weight:700;
        color:#111;
    }

    .plans-grid{
        display:grid;
        grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));
        gap:20px;
    }

    .plan-card{
        position:relative;
        background:#fff;
        border-radius:12px;
        padding:24px;
        box-shadow:0 1px 3px rgba(0,0,0,0.08);
        display:flex;
        flex-direction:column;
    }
    .plan-card--featured{
        border:2px solid #6fd82b;
    }
    .plan-card__badge{
        position:absolute;
        top:-12px;
        left:24px;
        background:#6fd82b;
        color:#0e2a04;
        font-size:11px;
        font-weight:700;
        padding:4px 10px;
        border-radius:999px;
    }
    .plan-card__name{
        margin:4px 0 4px;
        font-size:18px;
        font-weight:700;
        color:#111;
    }
    .plan-card__members{
        margin:0 0 16px;
        font-size:13px;
        color:#8a8a8a;
    }
    .plan-card__price{
        display:flex;
        align-items:baseline;
        gap:4px;
    }
    .plan-card__amount{
        font-size:32px;
        font-weight:700;
        color:#111;
    }
    .plan-card__period{
        font-size:14px;
        color:#8a8a8a;
    }
    .plan-card__subprice{
        margin:2px 0 20px;
        font-size:13px;
        color:#6fd82b;
        font-weight:600;
    }
    .plan-card__features{
        list-style:none;
        margin:0 0 24px;
        padding:0;
        flex-grow:1;
        display:flex;
        flex-direction:column;
        gap:10px;
        font-size:13.5px;
        color:#444;
    }
    .plan-card__features li{
        padding-left:22px;
        position:relative;
    }
    .plan-card__features li::before{
        content:"✓";
        position:absolute;
        left:0;
        color:#6fd82b;
        font-weight:700;
    }
    .plan-card__footer{
        display:flex;
        align-items:center;
        justify-content:space-between;
        border-top:1px solid #eee;
        padding-top:16px;
    }
    .plan-card__id{
        font-family:ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size:11px;
        color:#aaa;
    }
    .btn-edit-tier{
        text-decoration:none;
        font-size:13px;
        font-weight:600;
        color:#111;
        border:1px solid #ddd;
        border-radius:8px;
        padding:6px 14px;
    }
    .btn-edit-tier:hover{
        background:#f5f5f5;
    }
    .btn-edit-tier--featured{
        background:#6fd82b;
        color:#0e2a04;
        border-color:#6fd82b;
    }
    .btn-edit-tier--featured:hover{
        background:#63c724;
    }
</style>
@endsection 