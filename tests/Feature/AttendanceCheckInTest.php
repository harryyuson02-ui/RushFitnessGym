<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\AttendanceCheckedIn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AttendanceCheckInTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_admin_scans_an_active_membership_qr_to_record_attendance(): void
    {
        Notification::fake([AttendanceCheckedIn::class]);
        $admin = User::factory()->create();
        $memberUser = User::factory()->member()->create();
        $subscription = Subscription::factory()->create();
        $subscription->member->update(['user_id' => $memberUser->id]);

        $this->actingAs($admin)->postJson(route('admin.attendance.check-in'), [
            'qr_value' => route('admin.access.verify', $subscription->access_token),
        ])
            ->assertCreated()
            ->assertJsonPath('already_recorded', false)
            ->assertJsonPath('attendance.name', $subscription->member->full_name);

        $this->assertDatabaseHas('attendances', [
            'member_id' => $subscription->member_id,
            'subscription_id' => $subscription->id,
            'scanned_by' => $admin->id,
        ]);
        $this->assertSame(today()->toDateString(), Attendance::firstOrFail()->attendance_date->toDateString());
        Notification::assertSentTo(
            $memberUser,
            AttendanceCheckedIn::class,
            fn (AttendanceCheckedIn $notification): bool => $notification->attendance->member_id === $subscription->member_id,
        );
    }

    public function test_scanning_the_same_member_twice_does_not_duplicate_daily_attendance(): void
    {
        Notification::fake([AttendanceCheckedIn::class]);
        $admin = User::factory()->create();
        $memberUser = User::factory()->member()->create();
        $subscription = Subscription::factory()->create();
        $subscription->member->update(['user_id' => $memberUser->id]);
        $payload = ['qr_value' => route('admin.access.verify', $subscription->access_token)];

        $this->actingAs($admin)->postJson(route('admin.attendance.check-in'), $payload)->assertCreated();
        $this->postJson(route('admin.attendance.check-in'), $payload)
            ->assertOk()
            ->assertJsonPath('already_recorded', true);

        $this->assertSame(1, Attendance::whereBelongsTo($subscription->member)->count());
        Notification::assertSentToTimes($memberUser, AttendanceCheckedIn::class, 1);
    }

    public function test_invalid_or_expired_membership_qr_cannot_record_attendance(): void
    {
        $admin = User::factory()->create();
        $expired = Subscription::factory()->create([
            'expires_at' => today()->subDay(),
        ]);

        $this->actingAs($admin)->postJson(route('admin.attendance.check-in'), [
            'qr_value' => route('admin.access.verify', $expired->access_token),
        ])->assertUnprocessable();

        $this->postJson(route('admin.attendance.check-in'), [
            'qr_value' => 'https://example.com/not-a-rush-qr',
        ])->assertUnprocessable();

        $this->assertDatabaseEmpty('attendances');
    }

    public function test_only_admins_can_record_attendance(): void
    {
        $memberUser = User::factory()->member()->create();
        $subscription = Subscription::factory()->create();

        $this->actingAs($memberUser)->postJson(route('admin.attendance.check-in'), [
            'qr_value' => route('admin.access.verify', $subscription->access_token),
        ])->assertForbidden();

        $this->assertDatabaseEmpty('attendances');
    }

    public function test_admin_dashboard_shows_attendance_table_scanner_and_calendar_history(): void
    {
        $admin = User::factory()->create();
        $subscription = Subscription::factory()->create();
        $attendance = Attendance::query()->create([
            'member_id' => $subscription->member_id,
            'subscription_id' => $subscription->id,
            'scanned_by' => $admin->id,
            'attendance_date' => today(),
            'checked_in_at' => now()->setTime(8, 45),
        ]);

        $this->actingAs($admin)->get(route('admin.dashboard', [
            'attendance_month' => $attendance->attendance_date->format('Y-m'),
        ]))
            ->assertOk()
            ->assertSee('data-attendance-scanner', false)
            ->assertSee('aria-label="Scan member attendance QR"', false)
            ->assertSee('data-attendance-scan-dialog', false)
            ->assertSee('data-attendance-scan-video', false)
            ->assertSee('capture="environment"', false)
            ->assertSee('data-attendance-table', false)
            ->assertSee('data-attendance-calendar-open', false)
            ->assertSee('data-attendance-calendar-dialog', false)
            ->assertSee('data-attendance-calendar', false)
            ->assertSee('data-attendance-date="'.$attendance->attendance_date->toDateString().'"', false)
            ->assertSee('data-attendance-date-input', false)
            ->assertSee('data-attendance-calendar-apply', false)
            ->assertDontSee('data-attendance-day-panel', false)
            ->assertSeeText($subscription->member->full_name)
            ->assertSeeText('8:45 AM');
    }

    public function test_applying_a_calendar_date_filters_the_main_attendance_list(): void
    {
        $admin = User::factory()->create();
        $selectedSubscription = Subscription::factory()->create();
        $selectedSubscription->member->update(['first_name' => 'Selected', 'last_name' => 'Member']);
        $otherSubscription = Subscription::factory()->create();
        $otherSubscription->member->update(['first_name' => 'Other', 'last_name' => 'Member']);
        $selectedDate = today()->subDay();

        Attendance::query()->create([
            'member_id' => $selectedSubscription->member_id,
            'subscription_id' => $selectedSubscription->id,
            'scanned_by' => $admin->id,
            'attendance_date' => $selectedDate,
            'checked_in_at' => $selectedDate->copy()->setTime(9, 15),
        ]);
        Attendance::query()->create([
            'member_id' => $otherSubscription->member_id,
            'subscription_id' => $otherSubscription->id,
            'scanned_by' => $admin->id,
            'attendance_date' => today(),
            'checked_in_at' => now()->setTime(10, 30),
        ]);

        $this->actingAs($admin)->get(route('admin.dashboard', [
            'attendance_date' => $selectedDate->toDateString(),
        ]))
            ->assertOk()
            ->assertSeeText('Selected Member')
            ->assertDontSeeText('Other Member')
            ->assertSeeText($selectedDate->format('M j, Y'));
    }

    public function test_admin_dashboard_defaults_to_only_todays_attendance(): void
    {
        $admin = User::factory()->create();
        $todaySubscription = Subscription::factory()->create();
        $todaySubscription->member->update(['first_name' => 'Present', 'last_name' => 'Today']);
        $yesterdaySubscription = Subscription::factory()->create();
        $yesterdaySubscription->member->update(['first_name' => 'Present', 'last_name' => 'Yesterday']);

        Attendance::query()->create([
            'member_id' => $todaySubscription->member_id,
            'subscription_id' => $todaySubscription->id,
            'scanned_by' => $admin->id,
            'attendance_date' => today(),
            'checked_in_at' => now(),
        ]);
        Attendance::query()->create([
            'member_id' => $yesterdaySubscription->member_id,
            'subscription_id' => $yesterdaySubscription->id,
            'scanned_by' => $admin->id,
            'attendance_date' => today()->subDay(),
            'checked_in_at' => now()->subDay(),
        ]);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeText('Present Today')
            ->assertDontSeeText('Present Yesterday')
            ->assertSeeText('Today · '.today()->format('M j, Y'));
    }

    public function test_member_portal_polls_and_displays_new_attendance_notifications(): void
    {
        $admin = User::factory()->create();
        $memberUser = User::factory()->member()->create();
        $subscription = Subscription::factory()->create();
        $subscription->member->update(['user_id' => $memberUser->id]);
        $attendance = Attendance::query()->create([
            'member_id' => $subscription->member_id,
            'subscription_id' => $subscription->id,
            'scanned_by' => $admin->id,
            'attendance_date' => today(),
            'checked_in_at' => now(),
        ]);
        $memberUser->notify(new AttendanceCheckedIn($attendance));

        $this->actingAs($memberUser)->getJson(route('notifications.attendance'))
            ->assertOk()
            ->assertJsonPath('notifications.0.title', 'Attendance recorded')
            ->assertJsonPath('notifications.0.body', $memberUser->unreadNotifications()->firstOrFail()->data['body']);

        $this->get(route('member.dashboard'))
            ->assertOk()
            ->assertSee('data-attendance-toast', false)
            ->assertSee(route('notifications.attendance'), false)
            ->assertSeeText('Attendance recorded');
    }
}
