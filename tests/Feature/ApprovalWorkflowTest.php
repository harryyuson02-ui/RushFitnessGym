<?php

namespace Tests\Feature;

use App\Models\Coach;
use App\Models\CoachAssignmentRequest;
use App\Models\CoachAvailabilityChange;
use App\Models\Member;
use App\Models\ScheduleEvent;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\ApprovalDecision;
use App\Notifications\ApprovalRequested;
use Illuminate\Database\QueryException;
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

    public function test_coach_overview_calendar_opens_each_dates_schedule(): void
    {
        $this->travelTo('2026-10-01 09:00:00');
        $coachUser = User::factory()->coach()->create();
        $coach = Coach::factory()->for($coachUser)->create();
        $member = Member::factory()->for($coach)->create(['first_name' => 'Daily', 'last_name' => 'Client']);
        ScheduleEvent::factory()->for($coach)->for($member)->create([
            'title' => 'Overview Calendar Session',
            'starts_at' => '2026-10-15 14:00:00',
            'ends_at' => '2026-10-15 15:00:00',
        ]);
        $otherSession = ScheduleEvent::factory()->create([
            'title' => 'Hidden Other Coach Session',
            'starts_at' => '2026-10-15 16:00:00',
            'ends_at' => '2026-10-15 17:00:00',
        ]);

        $this->actingAs($coachUser)->get(route('coach.dashboard', ['schedule_month' => '2026-10']))
            ->assertOk()
            ->assertSeeText('October 2026')
            ->assertSee('data-coach-calendar', false)
            ->assertSee('data-coach-calendar-date="2026-10-15"', false)
            ->assertSee('data-coach-schedule-dialog', false)
            ->assertSee('data-coach-schedule-panel="2026-10-15"', false)
            ->assertSee('data-coach-schedule-close', false)
            ->assertSeeText('Overview Calendar Session')
            ->assertSeeText('Daily Client')
            ->assertDontSeeText($otherSession->title);
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

    public function test_a_member_can_have_only_one_pending_coach_assignment_request(): void
    {
        $member = Member::factory()->create(['coach_id' => null]);
        CoachAssignmentRequest::factory()->for($member)->create(['status' => 'pending']);

        $this->expectException(QueryException::class);

        CoachAssignmentRequest::factory()->for($member)->create(['status' => 'pending']);
    }

    public function test_admin_can_approve_an_application_with_a_requested_coach_and_create_one_training_request(): void
    {
        $admin = User::factory()->create();
        $coachUser = User::factory()->coach()->create();
        $coach = Coach::factory()->for($coachUser)->create();
        $memberUser = User::factory()->member()->create();
        $member = Member::factory()->for($memberUser)->create(['coach_id' => null]);
        $subscription = Subscription::factory()->for($member)->create([
            'requested_coach_id' => $coach->id,
            'status' => 'pending',
        ]);
        $pendingAccessToken = $subscription->access_token;
        $transaction = Transaction::factory()->for($member)->for($subscription)->create([
            'membership_plan_id' => $subscription->membership_plan_id,
            'status' => 'pending',
            'paid_at' => null,
        ]);
        Notification::fake();

        $this->actingAs($admin)->get(route('admin.approvals.index'))
            ->assertSeeText($member->full_name)
            ->assertSeeText($coach->name);

        $response = $this->actingAs($admin)->patch(route('admin.approvals.members.update', $subscription), [
            'decision' => 'approved',
        ]);

        $response->assertRedirect(route('admin.approvals.index'))->assertSessionHas('success');
        $this->assertSame('active', $subscription->fresh()->status);
        $this->assertNotSame($pendingAccessToken, $subscription->fresh()->access_token);
        $this->assertNotNull($subscription->fresh()->qr_issued_at);
        $this->assertSame('paid', $transaction->fresh()->status);
        $this->assertNull($member->fresh()->coach_id);
        $this->assertDatabaseCount('coach_assignment_requests', 1);
        $this->assertDatabaseHas('coach_assignment_requests', [
            'member_id' => $member->id,
            'coach_id' => $coach->id,
            'status' => 'pending',
        ]);
        Notification::assertSentTo(
            $coachUser,
            ApprovalRequested::class,
        );
        Notification::assertSentTo(
            $memberUser,
            ApprovalDecision::class,
            fn (ApprovalDecision $notification): bool => $notification->routeName === 'member.dashboard',
        );
    }

    public function test_approved_member_can_view_and_download_their_personalized_branded_qr(): void
    {
        $admin = User::factory()->create();
        $memberUser = User::factory()->member()->create();
        $member = Member::factory()->for($memberUser)->create([
            'first_name' => 'Ana',
            'last_name' => 'Reyes',
            'member_code' => 'RFG-QR-1001',
        ]);
        $subscription = Subscription::factory()->for($member)->create(['status' => 'pending']);

        $this->actingAs($admin)->patch(route('admin.approvals.members.update', $subscription), [
            'decision' => 'approved',
        ])->assertRedirect();

        $subscription->refresh();

        $this->actingAs($memberUser)->get(route('member.dashboard'))
            ->assertOk()
            ->assertSeeText('Your membership QR')
            ->assertSeeText('Ana Reyes')
            ->assertSee(route('access-key.image', $subscription->access_token), false)
            ->assertSeeText('Download membership QR');

        $qrResponse = $this->get(route('access-key.image', [
            'token' => $subscription->access_token,
            'download' => 1,
        ]));

        $qrResponse->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml')
            ->assertHeader('Content-Disposition', 'attachment; filename="RFG-QR-1001-access-qr.svg"')
            ->assertSee('RUSH', false)
            ->assertSee('FITNESS GYM', false)
            ->assertSee('Ana Reyes', false)
            ->assertSee('RFG-QR-1001', false);

        $otherMember = User::factory()->member()->create();
        $this->actingAs($otherMember)
            ->get(route('access-key.image', $subscription->access_token))
            ->assertForbidden();
    }

    public function test_repeated_admin_approval_does_not_create_a_duplicate_training_request(): void
    {
        $admin = User::factory()->create();
        $coach = Coach::factory()->create();
        $member = Member::factory()->create(['coach_id' => null]);
        $subscription = Subscription::factory()->for($member)->create([
            'requested_coach_id' => $coach->id,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->patch(route('admin.approvals.members.update', $subscription), [
            'decision' => 'approved',
        ])->assertRedirect();

        $this->actingAs($admin)->patch(route('admin.approvals.members.update', $subscription), [
            'decision' => 'approved',
        ])->assertStatus(409);

        $this->assertSame(1, $member->coachAssignmentRequests()->count());
    }

    public function test_coach_cannot_review_a_membership_before_admin_approval(): void
    {
        $coachUser = User::factory()->coach()->create();
        $coach = Coach::factory()->for($coachUser)->create();
        $member = Member::factory()->create(['coach_id' => null]);
        Subscription::factory()->for($member)->create([
            'requested_coach_id' => $coach->id,
            'status' => 'pending',
        ]);

        $this->actingAs($coachUser)->get(route('coach.approvals.index'))
            ->assertDontSeeText($member->full_name);
    }

    public function test_coach_training_queue_hides_membership_and_payment_information(): void
    {
        $coachUser = User::factory()->coach()->create();
        $coach = Coach::factory()->for($coachUser)->create();
        $member = Member::factory()->create([
            'coach_id' => null,
            'first_name' => 'Ana',
            'last_name' => 'Reyes',
            'fitness_goal' => 'Improve strength',
            'availability_days' => ['Monday', 'Wednesday'],
        ]);
        CoachAssignmentRequest::factory()->for($member)->for($coach)->create();

        $this->actingAs($coachUser)->get(route('coach.approvals.index'))
            ->assertSeeText('Training requests')
            ->assertSeeText('Ana Reyes')
            ->assertSeeText('Improve strength')
            ->assertSeeText('Monday, Wednesday')
            ->assertSeeText('Accept client')
            ->assertDontSeeText('Plan')
            ->assertDontSeeText('Payment')
            ->assertDontSee('₱')
            ->assertDontSeeText('Approve member');
    }

    public function test_coach_can_accept_their_training_request(): void
    {
        $coachUser = User::factory()->coach()->create();
        $coach = Coach::factory()->for($coachUser)->create();
        $memberUser = User::factory()->member()->create();
        $member = Member::factory()->for($memberUser)->create(['coach_id' => null]);
        $assignment = CoachAssignmentRequest::factory()->for($member)->for($coach)->create();
        Notification::fake([ApprovalDecision::class]);

        $response = $this->actingAs($coachUser)->patch(route('coach.approvals.update', $assignment), [
            'decision' => 'accepted',
        ]);

        $response->assertRedirect(route('coach.approvals.index'))->assertSessionHas('success');
        $this->assertSame('accepted', $assignment->fresh()->status);
        $this->assertSame($coach->id, $member->fresh()->coach_id);
    }

    public function test_repeated_coach_decision_returns_conflict_without_changing_assignment(): void
    {
        $coachUser = User::factory()->coach()->create();
        $coach = Coach::factory()->for($coachUser)->create();
        $member = Member::factory()->create(['coach_id' => null]);
        $assignment = CoachAssignmentRequest::factory()->for($member)->for($coach)->create();

        $this->actingAs($coachUser)->patch(route('coach.approvals.update', $assignment), [
            'decision' => 'accepted',
        ])->assertRedirect();

        $this->actingAs($coachUser)->patch(route('coach.approvals.update', $assignment), [
            'decision' => 'declined',
        ])->assertStatus(409);

        $this->assertSame('accepted', $assignment->fresh()->status);
        $this->assertSame($coach->id, $member->fresh()->coach_id);
    }

    public function test_coach_can_decline_a_training_request_without_declining_membership(): void
    {
        $coachUser = User::factory()->coach()->create();
        $coach = Coach::factory()->for($coachUser)->create();
        $member = Member::factory()->create(['coach_id' => null]);
        $subscription = Subscription::factory()->for($member)->create(['status' => 'active']);
        $assignment = CoachAssignmentRequest::factory()->for($member)->for($coach)->create();

        $this->actingAs($coachUser)->patch(route('coach.approvals.update', $assignment), [
            'decision' => 'declined',
        ])->assertRedirect(route('coach.approvals.index'));

        $this->assertSame('declined', $assignment->fresh()->status);
        $this->assertSame('active', $subscription->fresh()->status);
        $this->assertNull($member->fresh()->coach_id);
    }

    public function test_coach_cannot_review_another_coachs_training_request(): void
    {
        $coachUser = User::factory()->coach()->create();
        Coach::factory()->for($coachUser)->create();
        $assignment = CoachAssignmentRequest::factory()->create();

        $this->actingAs($coachUser)->patch(route('coach.approvals.update', $assignment), [
            'decision' => 'accepted',
        ])->assertNotFound();

        $this->assertSame('pending', $assignment->fresh()->status);
    }

    public function test_active_unassigned_member_can_request_an_available_coach(): void
    {
        $memberUser = User::factory()->member()->create();
        $member = Member::factory()->for($memberUser)->create(['coach_id' => null]);
        Subscription::factory()->for($member)->create(['status' => 'active']);
        $coachUser = User::factory()->coach()->create();
        $coach = Coach::factory()->for($coachUser)->create(['is_available' => true]);
        Notification::fake([ApprovalRequested::class]);

        $this->actingAs($memberUser)->post(route('member.coach-request.store'), [
            'coach_id' => $coach->id,
        ])->assertRedirect(route('member.dashboard'))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('coach_assignment_requests', [
            'member_id' => $member->id,
            'coach_id' => $coach->id,
            'status' => 'pending',
        ]);
        Notification::assertSentTo($coachUser, ApprovalRequested::class);
    }

    public function test_inactive_member_cannot_request_a_coach(): void
    {
        $memberUser = User::factory()->member()->create();
        $member = Member::factory()->for($memberUser)->create(['coach_id' => null]);
        Subscription::factory()->for($member)->create(['status' => 'declined']);

        $this->actingAs($memberUser)->post(route('member.coach-request.store'), [
            'coach_id' => Coach::factory()->create()->id,
        ])->assertForbidden();

        $this->assertDatabaseCount('coach_assignment_requests', 0);
    }

    public function test_member_cannot_request_another_coach_while_a_request_is_pending(): void
    {
        $memberUser = User::factory()->member()->create();
        $member = Member::factory()->for($memberUser)->create(['coach_id' => null]);
        Subscription::factory()->for($member)->create(['status' => 'active']);
        CoachAssignmentRequest::factory()->for($member)->create();

        $this->actingAs($memberUser)->post(route('member.coach-request.store'), [
            'coach_id' => Coach::factory()->create()->id,
        ])->assertForbidden();

        $this->assertSame(1, $member->coachAssignmentRequests()->count());
    }

    public function test_member_with_an_accepted_coach_cannot_request_another_coach(): void
    {
        $memberUser = User::factory()->member()->create();
        $member = Member::factory()->for($memberUser)->create();
        Subscription::factory()->for($member)->create(['status' => 'active']);

        $this->actingAs($memberUser)->post(route('member.coach-request.store'), [
            'coach_id' => Coach::factory()->create()->id,
        ])->assertForbidden();

        $this->assertDatabaseCount('coach_assignment_requests', 0);
    }

    public function test_pending_training_request_grants_neither_client_nor_chat_access(): void
    {
        $coachUser = User::factory()->coach()->create();
        $coach = Coach::factory()->for($coachUser)->create();
        $memberUser = User::factory()->member()->create();
        $member = Member::factory()->for($memberUser)->create(['coach_id' => null]);
        Subscription::factory()->for($member)->create(['status' => 'active']);
        CoachAssignmentRequest::factory()->for($member)->for($coach)->create();

        $this->actingAs($coachUser)->get(route('coach.clients.index'))
            ->assertOk()
            ->assertDontSeeText($member->full_name);
        $this->actingAs($coachUser)->get(route('chat.show', $memberUser))->assertForbidden();
        $this->actingAs($memberUser)->get(route('chat.show', $coachUser))->assertForbidden();
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
