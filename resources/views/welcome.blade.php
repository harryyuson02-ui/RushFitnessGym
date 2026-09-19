<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rush Fitness Gym</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    @vite('resources/css/style.css')
</head>
<body>

<main class="split">

    {{-- Left: the pitch --}}
    <section class="stage">
        <p class="mark">Rush Fitness Gym </p>

        <h1>Push yourself.<br>Stay strong.<br>Stay fit.</h1>

        <p class="lede">
            Open 7am to 9pm. Free weights, and machines six days a week.
        </p>

        <dl class="stats">
            <div>
                <dt>Coaches on the floor</dt>
                <dd>3</dd>
            </div>
            <div>
                <dt>Classes each week</dt>
                <dd>35+</dd>
            </div>
            <div>
                <dt>Members training here</dt>
                <dd>200+</dd>
            </div>
        </dl>

        <a href="https://rushfitnessgymtagumcity.com/" class="ghost-link">
        Browse For More Information
        </a>    </section>

    {{-- Right: sign in --}}
    <section class="gate">
        <div class="gate-inner">
            <h2>Sign in</h2>
            <p class="gate-sub">Choose how you use the gym.</p>

            <div class="roles" role="tablist" aria-label="Account type">
                <button type="button" class="role is-active" data-role="member" role="tab" aria-selected="true">
                    Member
                </button>
                <button type="button" class="role" data-role="staff" role="tab" aria-selected="false">
                    Coach or staff
                </button>
            </div>

            @if ($errors->any())
                <p class="alert">{{ $errors->first() }}</p>
            @endif

            <form method="POST" action="{{ url('/login') }}" class="form">
                @csrf
                <input type="hidden" name="role" id="role" value="{{ old('role', 'member') }}">

                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       autocomplete="username" required autofocus>

                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       autocomplete="current-password" required>

                <div class="row">
                    <label class="check">
                        <input type="checkbox" name="remember"> Keep me signed in
                    </label>
                    <a href="{{ url('/forgot-password') }}">Forgot password?</a>
                </div>

                <button type="submit" class="btn">Sign in</button>
            </form>

            <p class="foot" data-note="member">
                New here? <a href="{{ url('/register') }}">Create a member account</a>
            </p>
            <p class="foot" data-note="staff" hidden>
                Staff accounts are created by the gym admin.
            </p>
        </div>
    </section>

</main>

<script>
    const roleInput = document.getElementById('role');
    const notes = document.querySelectorAll('.foot');

    document.querySelectorAll('.role').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.role').forEach(function (t) {
                t.classList.toggle('is-active', t === tab);
                t.setAttribute('aria-selected', t === tab);
            });

            roleInput.value = tab.dataset.role;
            document.body.dataset.role = tab.dataset.role;

            notes.forEach(function (n) {
                n.hidden = n.dataset.note !== tab.dataset.role;
            });
        });
    });

    // Restore the tab after a failed login attempt
    document.querySelector('.role[data-role="' + roleInput.value + '"]')?.click();
</script>

</body>
</html>