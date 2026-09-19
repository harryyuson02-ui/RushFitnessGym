
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;

Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');

Route::post('/login', [LoginController::class, 'login'])
    ->name('login.submit');

Route::get('/register', function () {
    return view('register');
})->name('register');

Route::post('/register', function () {
    return redirect()->route('login');
})->name('register.store');

// Member Dashboard
Route::view('/member/dashboard', 'member.dashboard')
    ->name('member.dashboard');

// Coach Dashboard
Route::view('/coach/dashboard', 'coach.dashboard')
    ->name('coach.dashboard');

// Staff / Owner Shared Admin Dashboard
Route::view('/admin/dashboard', 'admin.dashboard')
    ->name('admin.dashboard');