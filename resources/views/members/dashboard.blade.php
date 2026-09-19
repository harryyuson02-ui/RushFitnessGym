<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Member Portal</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="container">
        <h1>Member Portal</h1>
        <p>Welcome, {{ auth()->user()->name ?? 'Member' }}!</p>

        <div class="member-card">
            <h3>Your Membership Pass</h3>
            <!-- Placeholder for Member QR Code / Access Pass -->
            <div class="qr-placeholder">
                <p>[ Your Digital QR Pass ]</p>
            </div>
            <p>Status: <span style="color:green;">Active</span></p>
        </div>
    </div>
</body>
</html>