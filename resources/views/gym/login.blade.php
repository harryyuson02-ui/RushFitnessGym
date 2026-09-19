<form method="POST" action="{{ route('gym.login.submit') }}">
    @csrf

    <input type="email" name="email" required placeholder="Email">
    <input type="password" name="password" required placeholder="Password">

    <button type="submit">Sign In</button>
</form>