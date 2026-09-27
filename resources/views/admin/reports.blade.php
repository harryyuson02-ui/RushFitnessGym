@extends('layouts.layout')

@section('title', 'Admin Reports')

@section('sidebar')
    @include('admin.sidebar')
@endsection

@section('content')

<div class="page-header">
    <h1>Reports</h1>
    <p>View gym statistics and reports.</p>
</div>

<div class="stats-grid">

    <div class="stat-card">
        <h3>Total Members</h3>
        <p>250</p>
    </div>

    <div class="stat-card">
        <h3>Monthly Revenue</h3>
        <p>₱125,000</p>
    </div>

    <div class="stat-card">
        <h3>Total Attendance</h3>
        <p>1,245</p>
    </div>

    <div class="stat-card">
        <h3>Active Memberships</h3>
        <p>180</p>
    </div>

</div>

<div class="content-card">

    <div class="card-header">
        <h2>Attendance Report</h2>
    </div>

    <table class="data-table">

        <thead>
            <tr>
                <th>Date</th>
                <th>Total Check-ins</th>
                <th>Members</th>
                <th>Coaches</th>
            </tr>
        </thead>

        <tbody>

            <tr>
                <td>September 22, 2026</td>
                <td>45</td>
                <td>40</td>
                <td>5</td>
            </tr>

            <tr>
                <td>September 21, 2026</td>
                <td>52</td>
                <td>46</td>
                <td>6</td>
            </tr>

            <tr>
                <td>September 20, 2026</td>
                <td>38</td>
                <td>34</td> 
                <td>4</td>
            </tr>

        </tbody>

    </table>

</div>

@endsection