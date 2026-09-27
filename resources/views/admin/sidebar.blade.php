<nav class="sidebar-nav">

    <a href="{{ route('admin.dashboard') }}"
       class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        Dashboard
    </a>

    <a href="{{ route('admin.attendance') }}"
       class="{{ request()->routeIs('admin.attendance') ? 'active' : '' }}">
        Attendance
    </a>

    <a href="{{ route('admin.membership') }}"
       class="{{ request()->routeIs('admin.membership') ? 'active' : '' }}">
        Membership
    </a>

    <a href="{{ route('admin.reports') }}"
       class="{{ request()->routeIs('admin.reports') ? 'active' : '' }}">
        Reports
    </a>

</nav>