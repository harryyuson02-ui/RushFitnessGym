
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register - Rush Fitness Gym</title>

    @vite('resources/css/style.css')
</head>
<body>

    <main class="gate">
        <div class="gate-inner">

            <h2>Create Account</h2>

            <p class="gate-sub">
                Join Rush Fitness Gym today.
            </p>

            <form method="POST"
                  action="{{ route('register.store') }}"
                  class="form">

                @csrf

                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>

                <label for="password_confirmation">
                    Confirm Password
                </label>

                <input type="password"
                       id="password_confirmation"
                       name="password_confirmation"
                       required>

                <button type="submit" class="btn">
                    Create Account
                </button>

            </form>

            <p class="foot">
                Already have an account?
                <a href="{{ route('login') }}">Sign in</a>
            </p>

        </div>
    </main>

</body>
</html>