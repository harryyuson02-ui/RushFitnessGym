<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;

// Public Welcome & Authentication Routes
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Authenticated Role Dashboards
Route::middleware(['auth'])->group(function () {

    // 1. Owner Dashboard Group
    Route::prefix('gym')->name('gym.')->group(function () {
        Route::view('/dashboard', 'gym.dashboard')->name('dashboard');
    });

    // 2. Staff & Coach Dashboard Group
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::view('/dashboard', 'staff.dashboard')->name('dashboard');
    });

    // 3. Member Dashboard Group
    Route::prefix('member')->name('member.')->group(function () {
        Route::view('/dashboard', 'member.dashboard')->name('dashboard');
    });
});