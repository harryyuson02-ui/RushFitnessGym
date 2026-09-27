<nav class="sidebar-nav">

    <a href="{{ route('coach.dashboard') }}"
       class="{{ request()->routeIs('coach.dashboard') ? 'active' : '' }}">
        Dashboard
    </a>

    <a href="{{ route('coach.attendance') }}"
       class="{{ request()->routeIs('coach.attendance') ? 'active' : '' }}">
        Attendance
    </a>

    <a href="{{ route('coach.clients') }}"
       class="{{ request()->routeIs('coach.clients') ? 'active' : '' }}">
        Clients
    </a>

</nav>