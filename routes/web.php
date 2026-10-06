<?php

use App\Http\Controllers\Admin\ApprovalController;
use App\Http\Controllers\Admin\CoachAvailabilityApprovalController;
use App\Http\Controllers\Admin\CoachController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GymAccessApprovalController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\MembershipPlanController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\SubscriptionQrController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\LoginCodeChallengeController;
use App\Http\Controllers\Auth\LoginCodeVerificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetChallengeController;
use App\Http\Controllers\Auth\PasswordResetVerificationController;
use App\Http\Controllers\Coach\ApprovalController as CoachApprovalController;
use App\Http\Controllers\Coach\AvailabilityController;
use App\Http\Controllers\Coach\ClientController;
use App\Http\Controllers\Coach\ClientRecordController;
use App\Http\Controllers\Coach\MemberApplicationApprovalController as CoachMemberApplicationApprovalController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Member\ApplicationController;
use App\Http\Controllers\Member\TransactionController as MemberTransactionController;
use App\Http\Controllers\Portal\CalendarController;
use App\Http\Controllers\Portal\ChatController;
use App\Http\Controllers\Portal\DashboardController as PortalDashboardController;
use App\Http\Controllers\Portal\ProfileController;
use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('welcome');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])
    ->middleware('throttle:6,1')
    ->name('login.submit');
Route::get('/register', [RegisterController::class, 'create'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])
    ->middleware('throttle:4,1')
    ->name('register.store');

Route::middleware('guest')->group(function (): void {
    Route::post('/login/code', [LoginCodeChallengeController::class, 'store'])->middleware('throttle:login-code')->name('login.code.send');
    Route::get('/login/code', [LoginCodeVerificationController::class, 'create'])->name('login.code');
    Route::post('/login/code/verify', [LoginCodeVerificationController::class, 'store'])->middleware('throttle:login-code-verify')->name('login.code.verify');
    Route::get('/forgot-password', [PasswordResetChallengeController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetChallengeController::class, 'store'])->middleware('throttle:password-reset-code')->name('password.send-code');
    Route::get('/forgot-password/code', [PasswordResetVerificationController::class, 'create'])->name('password.code');
    Route::post('/forgot-password/code', [PasswordResetVerificationController::class, 'store'])->middleware('throttle:password-reset-verify')->name('password.verify-code');
    Route::get('/reset-password', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->middleware('throttle:5,1')->name('password.update');
});

Route::get('/access-keys/{token}.svg', [SubscriptionQrController::class, 'image'])
    ->middleware('throttle:60,1')
    ->name('access-key.image');

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/account', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/account', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{contact}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{contact}', [ChatController::class, 'store'])->middleware('throttle:30,1')->name('chat.store');
    Route::patch('/notifications/{notification}', [NotificationController::class, 'update'])->name('notifications.update');
    Route::get('/calendar', [CalendarController::class, 'index'])->middleware('role:coach,user')->name('calendar.index');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::resource('members', MemberController::class)->only(['index']);
    Route::resource('coaches', CoachController::class);
    Route::resource('plans', MembershipPlanController::class)->parameters(['plans' => 'plan']);
    Route::resource('users', AdminUserController::class)->only(['index', 'create', 'store', 'edit', 'update']);
    Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
    Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::patch('/approvals/members/{subscription}', GymAccessApprovalController::class)->name('approvals.members.update');
    Route::patch('/approvals/availability/{availabilityChange}', CoachAvailabilityApprovalController::class)->name('approvals.availability.update');
    Route::get('/access/{token}', [SubscriptionQrController::class, 'verify'])->name('access.verify');
});

Route::prefix('coach')->name('coach.')->middleware(['auth', 'role:coach'])->group(function (): void {
    Route::get('/dashboard', [PortalDashboardController::class, 'coach'])->name('dashboard');
    Route::get('/approvals', [CoachApprovalController::class, 'index'])->name('approvals.index');
    Route::patch('/approvals/{subscription}', CoachMemberApplicationApprovalController::class)->name('approvals.update');
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/{member}', [ClientController::class, 'show'])->name('clients.show');
    Route::post('/clients/{member}/records', [ClientRecordController::class, 'store'])->name('clients.records.store');
    Route::get('/availability', [AvailabilityController::class, 'edit'])->name('availability.edit');
    Route::put('/availability', [AvailabilityController::class, 'update'])->name('availability.update');
});

Route::prefix('user')->name('member.')->middleware(['auth', 'role:user'])->group(function (): void {
    Route::get('/dashboard', [PortalDashboardController::class, 'member'])->name('dashboard');
    Route::post('/application', [ApplicationController::class, 'store'])->name('application.store');
    Route::get('/transactions', [MemberTransactionController::class, 'index'])->name('transactions.index');
});
