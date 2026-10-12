<?php

use App\Http\Controllers\Admin\ApprovalController;
use App\Http\Controllers\Admin\AttendanceCheckInController;
use App\Http\Controllers\Admin\CoachAvailabilityApprovalController;
use App\Http\Controllers\Admin\CoachController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GcashPaymentVerificationController;
use App\Http\Controllers\Admin\GymAccessApprovalController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\MembershipPlanController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\SubscriptionQrController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
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
use App\Http\Controllers\Member\CoachAssignmentController;
use App\Http\Controllers\Member\CoachRecordController as MemberCoachRecordController;
use App\Http\Controllers\Member\MembershipPlanController as MemberMembershipPlanController;
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
    Route::get('/forgot-password', [PasswordResetChallengeController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetChallengeController::class, 'store'])->middleware('throttle:password-reset-code')->name('password.send-code');
    Route::get('/forgot-password/code', [PasswordResetVerificationController::class, 'create'])->name('password.code');
    Route::post('/forgot-password/code', [PasswordResetVerificationController::class, 'store'])->middleware('throttle:password-reset-verify')->name('password.verify-code');
    Route::get('/reset-password', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->middleware('throttle:5,1')->name('password.update');
});

Route::get('/access-keys/{token}.svg', [SubscriptionQrController::class, 'image'])
    ->middleware(['auth', 'throttle:60,1'])
    ->name('access-key.image');

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/admin', fn () => redirect()->route('admin.dashboard'))
    ->middleware(['auth', 'admin'])
    ->name('admin.home');
Route::get('/coach', fn () => redirect()->route('coach.dashboard'))
    ->middleware(['auth', 'role:coach'])
    ->name('coach.home');
Route::get('/member', fn () => redirect()->route('member.dashboard'))
    ->middleware(['auth', 'role:user'])
    ->name('member.home');

Route::middleware('auth')->group(function (): void {
    Route::get('/account', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/account', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/messages/{message}/attachment', [ChatController::class, 'attachment'])->middleware('throttle:120,1')->name('chat.attachment');
    Route::delete('/chat/messages/{message}', [ChatController::class, 'destroy'])->middleware('throttle:30,1')->name('chat.destroy');
    Route::get('/chat/{contact}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{contact}', [ChatController::class, 'store'])->middleware('throttle:30,1')->name('chat.store');
    Route::patch('/notifications/{notification}', [NotificationController::class, 'update'])->name('notifications.update');
    Route::get('/notifications/attendance/latest', [NotificationController::class, 'attendance'])
        ->middleware('throttle:60,1')
        ->name('notifications.attendance');
    Route::get('/calendar', [CalendarController::class, 'index'])->middleware('role:coach,user')->name('calendar.index');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/attendance/check-in', AttendanceCheckInController::class)
        ->middleware('throttle:30,1')
        ->name('attendance.check-in');
    Route::resource('members', MemberController::class)->only(['index']);
    Route::resource('coaches', CoachController::class);
    Route::resource('plans', MembershipPlanController::class)->parameters(['plans' => 'plan']);
    Route::resource('users', AdminUserController::class)->only(['edit', 'update']);
    Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{transaction}/receipt', [AdminTransactionController::class, 'receipt'])
        ->middleware('throttle:60,1')
        ->name('transactions.receipt');
    Route::patch('/transactions/{transaction}/verification', GcashPaymentVerificationController::class)
        ->name('transactions.verification.update');
    Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::patch('/approvals/members/{subscription}', GymAccessApprovalController::class)->name('approvals.members.update');
    Route::patch('/approvals/availability/{availabilityChange}', CoachAvailabilityApprovalController::class)->name('approvals.availability.update');
    Route::get('/access/{token}', [SubscriptionQrController::class, 'verify'])->name('access.verify');
});

Route::prefix('coach')->name('coach.')->middleware(['auth', 'role:coach'])->group(function (): void {
    Route::get('/dashboard', [PortalDashboardController::class, 'coach'])->name('dashboard');
    Route::get('/approvals', [CoachApprovalController::class, 'index'])->name('approvals.index');
    Route::patch('/approvals/{assignmentRequest}', CoachMemberApplicationApprovalController::class)->name('approvals.update');
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/{member}', [ClientController::class, 'show'])->name('clients.show');
    Route::post('/clients/{member}/records', [ClientRecordController::class, 'store'])->name('clients.records.store');
    Route::get('/availability', [AvailabilityController::class, 'edit'])->name('availability.edit');
    Route::put('/availability', [AvailabilityController::class, 'update'])->name('availability.update');
});

Route::prefix('member')->name('member.')->middleware(['auth', 'role:user'])->group(function (): void {
    Route::get('/dashboard', [PortalDashboardController::class, 'member'])->name('dashboard');
    Route::get('/plans', [MemberMembershipPlanController::class, 'index'])->name('plans.index');
    Route::get('/records', [MemberCoachRecordController::class, 'index'])->name('records.index');
    Route::post('/application', [ApplicationController::class, 'store'])->name('application.store');
    Route::post('/coach-request', [CoachAssignmentController::class, 'store'])->name('coach-request.store');
    Route::get('/transactions', [MemberTransactionController::class, 'index'])->name('transactions.index');
});
