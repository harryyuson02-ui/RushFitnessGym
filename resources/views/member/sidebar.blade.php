<nav class="sidebar-nav">
    <a href="{{ route('member.dashboard') }}" class="{{ request()->routeIs('member.dashboard') ? 'active' : '' }}">Dashboard</a>
    <a href="{{ route('member.attendance') }}" class="{{ request()->routeIs('member.attendance') ? 'active' : '' }}">Attendance</a>
    <a href="{{ route('member.membership') }}" class="{{ request()->routeIs('member.membership') ? 'active' : '' }}">Membership</a>
    <a href="{{ route('member.profile') }}" class="{{ request()->routeIs('member.profile') ? 'active' : '' }}">Profile</a>
    <a href="{{ route('member.qr') }}" class="{{ request()->routeIs('member.qr') ? 'active' : '' }}">Generate QR Code</a>
</nav>