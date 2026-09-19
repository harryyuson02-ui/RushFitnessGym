<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff & Coach Portal</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="container">
        <h1>Staff & Coach Dashboard</h1>
        <p>Welcome back, {{ auth()->user()->name ?? 'Coach' }}!</p>

        <div class="actions">
            <!-- Quick links for daily staff operations -->
            <a href="{{ route('gym.checkin.scan') }}" class="btn">Scan Member QR</a>
            <a href="{{ route('gym.members.index') }}" class="btn">View Member List</a>
        </div>
    </div>
</body>
</html>