<?php

use App\Http\Controllers\Admin\CoachController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\MembershipPlanController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\SubscriptionQrController;
use App\Http\Controllers\Admin\SubscriptionRenewalController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('login.submit');
});

Route::get('/access-keys/{token}.svg', [SubscriptionQrController::class, 'image'])
    ->middleware('throttle:60,1')
    ->name('access-key.image');

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::resource('members', MemberController::class);
    Route::resource('coaches', CoachController::class);
    Route::resource('plans', MembershipPlanController::class)->parameters(['plans' => 'plan']);
    Route::post('/members/{member}/renewal', [SubscriptionRenewalController::class, 'store'])->name('members.renewal.store');
    Route::get('/access/{token}', [SubscriptionQrController::class, 'verify'])->name('access.verify');
    Route::patch('/notifications/{notification}', [NotificationController::class, 'update'])->name('notifications.update');
});
