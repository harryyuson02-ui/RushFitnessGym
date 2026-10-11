<?php

namespace Tests\Feature;

use App\Models\Coach;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminDirectoryTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_member_search_matches_full_name_and_keeps_status_filter(): void
    {
        $member = Member::factory()->create(['first_name' => 'Daniel', 'last_name' => 'Reyes']);
        Subscription::factory()->for($member)->create();
        $inactive = Member::factory()->create(['first_name' => 'Daniel', 'last_name' => 'Reyes']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.members.index', ['member_search' => 'Daniel Reyes', 'status' => 'active']))
            ->assertSee($member->member_code)
            ->assertDontSee($inactive->member_code);
    }

    public function test_expiring_filter_includes_today_and_three_days_but_not_four_or_expired(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 28)->setTime(12, 0));
        $today = Subscription::factory()->create(['expires_at' => '2026-09-28']);
        $soon = Subscription::factory()->create(['expires_at' => '2026-10-01']);
        $later = Subscription::factory()->create(['expires_at' => '2026-10-02']);
        $expired = Subscription::factory()->create(['expires_at' => '2026-09-27']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.members.index', ['status' => 'expiring']))
            ->assertSee($today->member->member_code)->assertSee($soon->member->member_code)
            ->assertDontSee($later->member->member_code)->assertDontSee($expired->member->member_code);
    }

    public function test_inactive_filter_uses_latest_subscription_and_includes_members_without_plans(): void
    {
        $member = Member::factory()->create();
        Subscription::factory()->for($member)->create();
        Subscription::factory()->for($member)->create(['expires_at' => today()->subDay()]);
        $withoutPlan = Member::factory()->create();
        $active = Subscription::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.members.index', ['status' => 'inactive']))
            ->assertSee($member->member_code)->assertSee($withoutPlan->member_code)
            ->assertDontSee($active->member->member_code);
    }

    public function test_pending_filter_includes_submitted_applications_and_excludes_them_from_inactive(): void
    {
        $pending = Subscription::factory()->create(['status' => 'pending']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.members.index', ['status' => 'pending']))
            ->assertSee($pending->member->member_code)
            ->assertSee('Pending');

        $this->get(route('admin.members.index', ['status' => 'inactive']))
            ->assertDontSee($pending->member->member_code);
    }

    public function test_member_id_search_and_pagination_preserve_query(): void
    {
        Member::factory()->count(13)->sequence(fn ($sequence) => ['member_code' => 'RFG-FILTER-'.$sequence->index])->create();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.members.index', ['member_search' => 'RFG-FILTER', 'status' => 'inactive']))
            ->assertViewHas('members', fn ($members) => $members->total() === 13 && $members->count() === 12
                && str_contains($members->nextPageUrl(), 'member_search=RFG-FILTER')
                && str_contains($members->nextPageUrl(), 'status=inactive'));
    }

    public function test_coaches_can_be_searched_by_specialty(): void
    {
        $boxing = Coach::factory()->create(['name' => 'Jordan Cruz', 'specialty' => 'Boxing']);
        $other = Coach::factory()->create(['name' => 'Alex Rivera', 'specialty' => 'Strength']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.coaches.index', ['search' => 'Boxing']))
            ->assertSee($boxing->name)->assertDontSee($other->name);
    }

    public function test_no_matching_coach_search_shows_empty_state(): void
    {
        Coach::factory()->create(['name' => 'Alex Rivera', 'specialty' => 'Strength']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.coaches.index', ['search' => 'NoSuchCoach']))
            ->assertSee('No matching coaches');
    }

    public function test_admin_coach_form_uses_a_weekly_calendar_instead_of_schedule_text(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->get(route('admin.coaches.create'))
            ->assertSee('Weekly schedule calendar')
            ->assertSee('name="password"', false)
            ->assertSee('name="password_confirmation"', false)
            ->assertSee('type="time"', false)
            ->assertDontSee('textarea name="schedule"', false);
    }

    public function test_member_cannot_open_or_change_the_admin_coach_calendar(): void
    {
        $member = User::factory()->member()->create();

        $this->actingAs($member)->get(route('admin.coaches.create'))->assertForbidden();
        $this->actingAs($member)->post(route('admin.coaches.store'), [])->assertForbidden();
    }

    public function test_admin_can_create_a_coach_with_calendar_availability(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.coaches.store'), [
            'name' => 'Calendar Coach',
            'phone' => '09170000000',
            'email' => 'calendar-coach@example.test',
            'password' => 'coach-password123',
            'password_confirmation' => 'coach-password123',
            'specialty' => Coach::SPECIALTIES[0],
            'is_available' => '1',
            'availability' => $this->weeklyAvailability([1, 3]),
        ]);

        $coach = Coach::where('email', 'calendar-coach@example.test')->firstOrFail();

        $response->assertRedirect(route('admin.coaches.index'));
        $this->assertSame('Mon 8:00 AM–5:00 PM · Wed 8:00 AM–5:00 PM', $coach->schedule);
        $this->assertSame(2, $coach->availabilitySlots()->count());
        $this->assertDatabaseHas('availability_slots', ['coach_id' => $coach->id, 'day_of_week' => 1]);
        $this->assertDatabaseHas('availability_slots', ['coach_id' => $coach->id, 'day_of_week' => 3]);
    }

    public function test_admin_chosen_coach_password_allows_access_to_assigned_clients(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.coaches.store'), [
            'name' => 'New Coach',
            'phone' => '09170000000',
            'email' => 'new-coach@example.test',
            'specialty' => Coach::SPECIALTIES[0],
            'is_available' => '1',
            'availability' => $this->weeklyAvailability([1]),
            'role' => 'admin',
            'password' => 'chosen-coach-password123',
            'password_confirmation' => 'chosen-coach-password123',
        ]);

        $response->assertRedirect(route('admin.coaches.index'))->assertSessionMissing('coach_credentials');
        $coach = Coach::where('email', 'new-coach@example.test')->firstOrFail();
        $this->assertDatabaseHas('users', ['id' => $coach->user_id, 'email' => 'new-coach@example.test', 'role' => 'coach']);
        $this->assertTrue(Hash::check('chosen-coach-password123', $coach->user->password));
        $this->assertAuthenticatedAs($admin);

        $assignedClient = Member::factory()->for($coach)->create();
        Subscription::factory()->for($assignedClient)->create();
        $otherClient = Member::factory()->create();
        Subscription::factory()->for($otherClient)->create();
        $this->post(route('logout'));

        $this->post(route('login.submit'), ['email' => 'new-coach@example.test', 'password' => 'chosen-coach-password123'])
            ->assertRedirect(route('coach.dashboard'));
        $this->assertAuthenticatedAs($coach->user);
        $this->get(route('coach.dashboard'))->assertOk();
        $this->get(route('coach.clients.index'))->assertSeeText($assignedClient->full_name)->assertDontSeeText($otherClient->full_name);
        $this->get(route('coach.clients.show', $otherClient))->assertNotFound();
        $this->get(route('admin.coaches.index'))->assertForbidden();

        $this->put(route('profile.update'), [
            'current_password' => 'chosen-coach-password123',
            'password' => 'my-new-coach-password123',
            'password_confirmation' => 'my-new-coach-password123',
        ])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('my-new-coach-password123', $coach->user->fresh()->password));
        $this->assertFalse(Hash::check('chosen-coach-password123', $coach->user->fresh()->password));
    }

    public function test_coach_creation_requires_matching_password_confirmation(): void
    {
        $this->actingAs(User::factory()->create())->post(route('admin.coaches.store'), [
            'name' => 'Password Coach',
            'phone' => '09170000000',
            'email' => 'password-coach@example.test',
            'password' => 'chosen-coach-password123',
            'password_confirmation' => 'different-password123',
            'specialty' => Coach::SPECIALTIES[0],
            'availability' => $this->weeklyAvailability([1]),
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'password-coach@example.test']);
        $this->assertDatabaseMissing('coaches', ['email' => 'password-coach@example.test']);
    }

    public function test_coach_creation_rejects_an_email_already_used_for_login(): void
    {
        $admin = User::factory()->create();
        User::factory()->member()->create(['email' => 'existing@example.test']);

        $this->actingAs($admin)->post(route('admin.coaches.store'), [
            'name' => 'Duplicate Coach',
            'phone' => '09170000000',
            'email' => 'existing@example.test',
            'specialty' => Coach::SPECIALTIES[0],
            'availability' => $this->weeklyAvailability([1]),
        ])->assertSessionHasErrors('email')->assertSessionMissing('coach_credentials');

        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('coaches', 0);
    }

    public function test_coach_creation_requires_a_login_email(): void
    {
        $this->actingAs(User::factory()->create())->post(route('admin.coaches.store'), [
            'name' => 'Missing Email Coach',
            'phone' => '09170000000',
            'specialty' => Coach::SPECIALTIES[0],
            'availability' => $this->weeklyAvailability([1]),
        ])->assertSessionHasErrors('email')->assertSessionMissing('coach_credentials');

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('coaches', 0);
    }

    #[DataProvider('invalidCoachMobileNumbers')]
    public function test_admin_cannot_create_or_update_a_coach_with_an_invalid_mobile_number(mixed $phone): void
    {
        $admin = User::factory()->create();
        $coach = Coach::factory()->create(['phone' => '09171234567']);
        $payload = [
            'name' => 'Phone Validation Coach',
            'phone' => $phone,
            'email' => 'phone-coach@example.test',
            'specialty' => Coach::SPECIALTIES[0],
            'availability' => $this->weeklyAvailability([1]),
        ];
        $error = 'Enter a Philippine mobile number with exactly 11 digits starting with 09 (e.g. 09171234567).';

        $this->actingAs($admin)->post(route('admin.coaches.store'), $payload)
            ->assertSessionHasErrors(['phone' => $error]);
        $this->put(route('admin.coaches.update', $coach), $payload)
            ->assertSessionHasErrors(['phone' => $error]);

        $this->assertDatabaseCount('coaches', 1);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('coaches', ['id' => $coach->id, 'phone' => '09171234567']);
        $this->assertDatabaseCount('availability_slots', 0);
    }

    /** @return array<string, array{mixed}> */
    public static function invalidCoachMobileNumbers(): array
    {
        return [
            'letters' => ['09abcdefghi'],
            'embedded letter' => ['0917a1234567'],
            'too short' => ['0917123456'],
            'too long' => ['091712345678'],
            'wrong prefix' => ['08171234567'],
            'international format' => ['+639171234567'],
            'spaces' => ['0917 1234567'],
            'symbols' => ['0917-1234567'],
            'empty' => [''],
            'array' => [['09171234567']],
        ];
    }

    public function test_admin_can_replace_a_coachs_calendar_availability(): void
    {
        $admin = User::factory()->create();
        $coach = Coach::factory()->create();
        $coach->availabilitySlots()->create([
            'day_of_week' => 1,
            'starts_at' => '08:00',
            'ends_at' => '17:00',
            'is_available' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.coaches.update', $coach), [
            'name' => $coach->name,
            'phone' => $coach->phone,
            'email' => $coach->email,
            'specialty' => $coach->specialty,
            'is_available' => '1',
            'availability' => $this->weeklyAvailability([2], '10:00', '18:00'),
        ]);

        $response->assertRedirect(route('admin.coaches.index'));
        $this->assertSame('Tue 10:00 AM–6:00 PM', $coach->fresh()->schedule);
        $this->assertDatabaseMissing('availability_slots', ['coach_id' => $coach->id, 'day_of_week' => 1]);
        $this->assertDatabaseHas('availability_slots', [
            'coach_id' => $coach->id,
            'day_of_week' => 2,
            'starts_at' => '10:00',
            'ends_at' => '18:00',
        ]);
    }

    public function test_calendar_availability_rejects_an_end_time_before_the_start_time(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->from(route('admin.coaches.create'))->post(route('admin.coaches.store'), [
            'name' => 'Invalid Calendar Coach',
            'phone' => '09170000000',
            'specialty' => Coach::SPECIALTIES[0],
            'is_available' => '1',
            'availability' => $this->weeklyAvailability([1], '17:00', '08:00'),
        ])->assertRedirect(route('admin.coaches.create'))
            ->assertSessionHasErrors([
                'availability.1.ends_at' => 'The end time must be after the start time.',
            ]);

        $this->assertDatabaseMissing('coaches', ['name' => 'Invalid Calendar Coach']);
    }

    public function test_plans_display_live_prices_and_duration_based_images(): void
    {
        MembershipPlan::factory()->create(['name' => 'Monthly', 'duration_days' => 30, 'price' => 500]);
        MembershipPlan::factory()->create(['name' => 'Quarterly', 'duration_days' => 90, 'price' => 1500]);
        MembershipPlan::factory()->create(['name' => 'Annual', 'duration_days' => 365, 'price' => 6000]);

        $this->actingAs(User::factory()->create())->get(route('admin.plans.index'))
            ->assertSee('₱500.00')->assertSee('₱1,500.00')->assertSee('₱6,000.00')
            ->assertSee('images/plans/monthly.jpg')->assertSee('images/plans/quarterly.jpg')->assertSee('images/plans/annual.jpg');
    }

    public function test_directory_routes_remain_admin_only(): void
    {
        foreach (['admin.members.index', 'admin.coaches.index', 'admin.plans.index'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
        $this->actingAs(User::factory()->nonAdmin()->create());
        foreach (['admin.members.index', 'admin.coaches.index', 'admin.plans.index'] as $route) {
            $this->get(route($route))->assertForbidden();
        }
    }

    public function test_malformed_directory_filters_are_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->from(route('admin.members.index'))
            ->get(route('admin.members.index', ['member_search' => ['bad'], 'status' => 'unknown']))
            ->assertSessionHasErrors(['member_search', 'status']);
    }

    /**
     * @param  array<int, int>  $enabledDays
     * @return array<int, array{day_of_week: int, enabled?: string, starts_at: string, ends_at: string}>
     */
    private function weeklyAvailability(array $enabledDays, string $startsAt = '08:00', string $endsAt = '17:00'): array
    {
        return collect(range(0, 6))->mapWithKeys(fn (int $day): array => [
            $day => array_filter([
                'day_of_week' => $day,
                'enabled' => in_array($day, $enabledDays, true) ? '1' : null,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ], fn (mixed $value): bool => $value !== null),
        ])->all();
    }
}
