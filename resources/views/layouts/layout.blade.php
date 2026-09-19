
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Rush Fitness Gym')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/style.css')
</head>

<body>

    <div class="dashboard-layout">

        <!-- Sidebar -->
        <aside class="sidebar">

            <div class="sidebar-brand">
                <h2>RUSH</h2>
                <span>FITNESS GYM</span>
            </div>

            <nav class="sidebar-nav">

                <a href="#" class="nav-link active">
                    Dashboard
                </a>

                <a href="#" class="nav-link">
                    Membership
                </a>

                <a href="#" class="nav-link">
                    Attendance
                </a>

                <a href="#" class="nav-link">
                    Profile
                </a>

            </nav>

            <div class="sidebar-bottom">
                <a href="{{ route('login') }}" class="nav-link">
                    Logout
                </a>
            </div>

        </aside>

        <!-- Main Content -->
        <main class="main-content">

            <header class="dashboard-header">

                <div>
                    <p class="header-label">RUSH FITNESS GYM</p>
                    <h1>@yield('header', 'Dashboard')</h1>
                </div>

                <div class="header-badge">
                    MEMBER
                </div>

            </header>

            <section class="dashboard-content">
                @yield('content')
            </section>

        </main>

    </div>

</body>
</html>