<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Show Welcome Page with UI tabs
    public function showLoginForm()
    {
        return view('welcome');
    }

    // Process authentication and direct user based on role
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
            'role'     => ['required', 'in:member,staff,owner'],
        ]);

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Direct route matching based on role
            if ($user->role === 'owner') {
                return redirect()->route('gym.dashboard');
            } elseif ($user->role === 'staff' || $user->role === 'coach') {
                return redirect()->route('staff.dashboard');
            } else {
                return redirect()->route('member.dashboard');
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email', 'role');
    }

    // Logout logic
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}