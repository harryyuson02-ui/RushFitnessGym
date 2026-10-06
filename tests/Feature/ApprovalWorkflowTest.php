<?php

namespace Tests\Feature;

use App\Models\Coach;
use App\Models\CoachAvailabilityChange;
use App\Models\Member;
use App\Models\ScheduleEvent;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\ApprovalDecision;
use App\Notifications\ApprovalRequested;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ApprovalWorkflowTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_coach_calendar_is_a_visual_month_of_only_their_client_sessions(): void
    {
        $this->travelTo('2026-10-01 09:00:00');
        $coachUser = User::factory()->coach()->create();
        $coach = Coach::factory()->for($coachUser)->create();
        $member = Member::factory()->for($coach)->create(['first_name' => 'Ana', 'last_name' => 'Reyes']);
        ScheduleEvent::factory()->for($coach)->for($member)->create([
            'title' => 'Strength Session', 'type' => 'session',
            'starts_at' => '2026-10-15 09:00:00', 'ends_at' => '2026-10-15 10:00:00',
        ]);
        ScheduleEvent::factory()->for($coach)->for($member)->create([
            'title' => 'Private Assessment', 'type' => 'assessment',
            'starts_at' => '2026-10-16 09:00:00', 'ends_at' => '2026-10-16 10:00:00',
        ]);
        $otherSession = ScheduleEvent::factory()->create([
            'title' => 'Other Coach Session',
            'starts_at' => '2026-10-15 11:00:00', 'ends_at' => '2026-10-15 12:00:00',
        ]);

        $this->actingAs($coachUser)->get(route('calendar.index', ['month' => '2026-10']))
            ->assertSee('October 2026')->assertSee('Strength Session')->assertSee('Ana Reyes')
            ->assertDontSee('Private Assessment')->assertDontSee($otherSession->title)->assertDontSee('Add event');
    }

    public function test_coach_availability_update_waits_for_admin_approval(): void
    {
        Notification::fake([ApprovalRequested::class]);
        $admin = User::factory()->create();
        $coachUser = User::factory()->coach()->create();
        $coach = Coach::factory()->for($coachUser)->create(['schedule' => 'Mon 8:00 AM–5:00 PM']);
        $coach->availabilitySlots()->create([
            'day_of_week' => 1, 'starts_at' => '08:00', 'ends_at' => '17:00', 'is_available' => true,
        ]);

        $response = $this->actingAs($coachUser)->put(route('coach.availability.update'), [
            'is_available' => '1',
            'slots' => $this->weeklySlots([2], '10:00', '18:00'),
        ]);

        $response->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('coach_availability_changes', ['coach_id' => $coach->id, 'status' => 'pending']);
        $this->assertDatabaseHas('availability_slots', ['coach_id' => $coach->id, 'day_of_week' => 1, 'starts_at' => '08:00']);
        $this->assertSame('Mon 8:00 AM–5:00 PM', $coach->fresh()->schedule);
        Notification::assertSentTo($admin, ApprovalRequested::class);
    }

    public function test_admin_approval_applies_the_coach_schedule_update(): void
    {
        Notification::fake([ApprovalDecision::class]);
        $admin = User::factory()->create();
        $coachUser = User::factory()->coach()->create();
        $coach = Coach::factory()->for($coachUser)->create(['schedule' => 'Mon 8:00 AM–5:00 PM']);
        $coach->availabilitySlots()->create([
            'day_of_week' => 1, 'starts_at' => '08:00', 'ends_at' => '17:00', 'is_available' => true,
        ]);
        $change = CoachAvailabilityChange::factory()->for($coach)->create([
            'submitted_by' => $coachUser->id,
            'slots' => [[
                'day_of_week' => 2, 'starts_at' => '10:00', 'ends_at' => '18:00', 'is_available' => true,
            ]],
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.approvals.availability.update', $change), [
            'decision' => 'approved',
        ]);

        $response->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('coach_availability_changes', [
            'id' => $change->id, 'status' => 'approved', 'reviewed_by' => $admin->id,
        ]);
        $this->assertDatabaseMissing('availability_slots', ['coach_id' => $coach->id, 'day_of_week' => 1]);
        $this->assertDatabaseHas('availability_slots', ['coach_id' => $coach->id, 'day_of_week' => 2]);
        $this->assertSame('Tue 10:00 AM–6:00 PM', $coach->fresh()->schedule);
        Notification::assertSentTo($coachUser, ApprovalDecision::class);
    }

    public function test_admin_declining_a_coach_schedule_keeps_the_approved_schedule(): void
    {
        $admin = User::factory()->create();
        $coachUser = User::factory()->coach()->create();
        $coach = Coach::factory()->for($coachUser)->create(['schedule' => 'Mon 8:00 AM–5:00 PM']);
        $coach->availabilitySlots()->create([
            'day_of_week' => 1, 'starts_at' => '08:00', 'ends_at' => '17:00', 'is_available' => true,
        ]);
        $change = CoachAvailabilityChange::factory()->for($coach)->create([
            'submitted_by' => $coachUser->id,
            'slots' => [[
                'day_of_week' => 2, 'starts_at' => '10:00', 'ends_at' => '18:00', 'is_available' => true,
            ]],
        ]);

        $this->actingAs($admin)->patch(route('admin.approvals.availability.update', $change), [
            'decision' => 'declined',
        ])->assertRedirect();

        $this->assertSame('declined', $change->fresh()->status);
        $this->assertSame('Mon 8:00 AM–5:00 PM', $coach->fresh()->schedule);
        $this->assertDatabaseHas('availability_slots', ['coach_id' => $coach->id, 'day_of_week' => 1]);
        $this->assertDatabaseMissing('availability_slots', ['coach_id' => $coach->id, 'day_of_week' => 2]);
    }

    public function test_admin_can_search_pending_schedule_updates_by_coach_first_name(): void
    {
        $matchingCoach = Coach::factory()->create(['name' => 'Jordan Cruz']);
        $otherCoach = Coach::factory()->create(['name' => 'Alex Jordan']);
        CoachAvailabilityChange::factory()->for($matchingCoach)->create();
        CoachAvailabilityChange::factory()->for($otherCoach)->create();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.approvals.index', ['search' => 'Jor']))
            ->assertSeeText('Jordan Cruz')
            ->assertDontSeeText('Alex Jordan')
            ->assertSee('value="Jor"', false);
    }

    public function test_coach_approval_queue_only_shows_members_who_selected_that_coach(): void
    {
        $coachUser = User::factory()->coach()->create();
        $coach = Coach::factory()->for($coachUser)->create();
        $otherCoach = Coach::factory()->create();
        $ownMember = Member::factory()->for($coach)->create([
            'first_name' => 'Ana',
            'last_name' => 'Reyes',
            'fitness_goal' => 'Improve strength',
            'availability_days' => ['Monday', 'Wednesday'],
        ]);
        $otherMember = Member::factory()->for($otherCoach)->create(['first_name' => 'Private', 'last_name' => 'Applicant']);
        Subscription::factory()->for($ownMember)->create(['status' => 'pending']);
        Subscription::factory()->for($otherMember)->create(['status' => 'pending']);

        $this->actingAs($coachUser)->get(route('coach.approvals.index'))
            ->assertSeeText('Ana Reyes')
            ->assertSeeText('Improve strength')
            ->assertSeeText('Monday, Wednesday')
            ->assertSeeText('Approve member')
            ->assertDontSeeText('Private Applicant');
    }

    public function test_selected_coach_can_approve_a_pending_member_application_and_notifies_member(): void
    {
        $coachUser = User::factory()->coach()->create();
        $coach = Coach::factory()->for($coachUser)->create();
        $memberUser = User::factory()->member()->create();
        $member = Member::factory()->for($memberUser)->for($coach)->create();
        $subscription = Subscription::factory()->for($member)->create(['status' => 'pending']);
        $transaction = Transaction::factory()->for($member)->for($subscription)->create([
            'membership_plan_id' => $subscription->membership_plan_id,
            'status' => 'pending',
            'paid_at' => null,
        ]);
        Notification::fake([ApprovalDecision::class]);

        $response = $this->actingAs($coachUser)->patch(route('coach.approvals.update', $subscription), [
            'decision' => 'approved',
        ]);

        $response->assertRedirect(route('coach.approvals.index'))->assertSessionHas('success');
        $this->assertSame('active', $subscription->fresh()->status);
        $this->assertSame('paid', $transaction->fresh()->status);
        Notification::assertSentTo(
            $memberUser,
            ApprovalDecision::class,
            fn (ApprovalDecision $notification): bool => $notification->title === 'Membership application approved',
        );
    }

    public function test_selected_coach_can_decline_a_pending_member_application_and_notifies_member(): void
    {
        $coachUser = User::factory()->coach()->create();
        $coach = Coach::factory()->for($coachUser)->create();
        $memberUser = User::factory()->member()->create();
        $member = Member::factory()->for($memberUser)->for($coach)->create();
        $subscription = Subscription::factory()->for($member)->create(['status' => 'pending']);
        $transaction = Transaction::factory()->for($member)->for($subscription)->create([
            'membership_plan_id' => $subscription->membership_plan_id, 'status' => 'pending',
        ]);
        Notification::fake([ApprovalDecision::class]);

        $this->actingAs($coachUser)->patch(route('coach.approvals.update', $subscription), [
            'decision' => 'declined',
        ])->assertRedirect(route('coach.approvals.index'));

        $this->assertSame('declined', $subscription->fresh()->status);
        $this->assertSame('rejected', $transaction->fresh()->status);
        Notification::assertSentTo(
            $memberUser,
            ApprovalDecision::class,
            fn (ApprovalDecision $notification): bool => $notification->title === 'Membership application declined',
        );
    }

    public function test_coach_cannot_review_another_coachs_member_application(): void
    {
        $coachUser = User::factory()->coach()->create();
        Coach::factory()->for($coachUser)->create();
        $otherCoach = Coach::factory()->create();
        $member = Member::factory()->for($otherCoach)->create();
        $subscription = Subscription::factory()->for($member)->create(['status' => 'pending']);

        $this->actingAs($coachUser)->patch(route('coach.approvals.update', $subscription), [
            'decision' => 'approved',
        ])->assertNotFound();

        $this->assertSame('pending', $subscription->fresh()->status);
    }

    public function test_admin_can_approve_a_gym_only_member_application(): void
    {
        $admin = User::factory()->create();
        $memberUser = User::factory()->member()->create();
        $member = Member::factory()->for($memberUser)->create(['coach_id' => null]);
        $subscription = Subscription::factory()->for($member)->create(['status' => 'pending']);
        $transaction = Transaction::factory()->for($member)->for($subscription)->create([
            'membership_plan_id' => $subscription->membership_plan_id,
            'status' => 'pending',
            'paid_at' => null,
        ]);
        $coachedMember = Member::factory()->create(['first_name' => 'Coach', 'last_name' => 'Queue Only']);
        Subscription::factory()->for($coachedMember)->create(['status' => 'pending']);
        Notification::fake([ApprovalDecision::class]);

        $this->actingAs($admin)->get(route('admin.approvals.index'))
            ->assertSeeText($member->full_name)
            ->assertDontSeeText('Coach Queue Only')
            ->assertSeeText('Gym-only membership applications');
        $response = $this->actingAs($admin)->patch(route('admin.approvals.members.update', $subscription), [
            'decision' => 'approved',
        ]);

        $response->assertRedirect(route('admin.approvals.index'))->assertSessionHas('success');
        $this->assertSame('active', $subscription->fresh()->status);
        $this->assertSame('paid', $transaction->fresh()->status);
        Notification::assertSentTo(
            $memberUser,
            ApprovalDecision::class,
            fn (ApprovalDecision $notification): bool => $notification->title === 'Membership application approved',
        );
    }

    public function test_admin_can_decline_a_gym_only_member_application(): void
    {
        $admin = User::factory()->create();
        $memberUser = User::factory()->member()->create();
        $member = Member::factory()->for($memberUser)->create(['coach_id' => null]);
        $subscription = Subscription::factory()->for($member)->create(['status' => 'pending']);
        $transaction = Transaction::factory()->for($member)->for($subscription)->create([
            'membership_plan_id' => $subscription->membership_plan_id,
            'status' => 'pending',
        ]);
        Notification::fake([ApprovalDecision::class]);

        $this->actingAs($admin)->patch(route('admin.approvals.members.update', $subscription), [
            'decision' => 'declined',
        ])->assertRedirect(route('admin.approvals.index'));

        $this->assertSame('declined', $subscription->fresh()->status);
        $this->assertSame('rejected', $transaction->fresh()->status);
        Notification::assertSentTo(
            $memberUser,
            ApprovalDecision::class,
            fn (ApprovalDecision $notification): bool => $notification->title === 'Membership application declined',
        );
    }

    public function test_admin_cannot_review_a_membership_application_assigned_to_a_coach(): void
    {
        $admin = User::factory()->create();
        $member = Member::factory()->create();
        $subscription = Subscription::factory()->for($member)->create(['status' => 'pending']);

        $this->actingAs($admin)->patch(route('admin.approvals.members.update', $subscription), [
            'decision' => 'approved',
        ])->assertNotFound();

        $this->assertSame('pending', $subscription->fresh()->status);
    }

    /**
     * @param  array<int, int>  $enabledDays
     * @return array<int, array{day_of_week: int, enabled?: string, starts_at: string, ends_at: string}>
     */
    private function weeklySlots(array $enabledDays, string $startsAt, string $endsAt): array
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
