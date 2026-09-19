<?php
    Namespace App\Http\Controllers;

    use Illuminate\Http\Request;

class LoginController extends Controller
    {
    public function showLoginForm()
    {
        return view('welcome');
    }

     
public function login(Request $request)
{
    $role = $request->input('role');

    // Staff and Owner use the same dashboard
    if ($role === 'owner' || $role === 'staff' || $role === 'coach') {

        return redirect()->route('admin.dashboard');

    }

    // Member Dashboard
    return redirect()->route('member.dashboard');
}
        public function logout(Request $request)
    {
            return redirect()->route('login');
    }
}