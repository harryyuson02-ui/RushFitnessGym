<?php

namespace Tests\Feature;

use App\Models\Coach;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Message;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\ApprovalRequested;
use App\Notifications\NewMessageReceived;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PortalWorkflowsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_role_accounts_sign_in_to_their_own_dashboard(): void
    {
        foreach (['coach' => 'coach.dashboard', 'user' => 'member.dashboard'] as $role => $routeName) {
            $user = User::factory()->create(['role' => $role]);

            $this->post(route('login.submit'), ['email' => $user->email, 'password' => 'password'])
                ->assertRedirect(route($routeName));

            $this->post(route('logout'));
        }
    }

    public function test_each_role_has_its_own_portal_with_password_access_in_the_avatar_menu(): void
    {
        foreach ([
            'admin' => ['admin.dashboard', 'Administration'],
            'coach' => ['coach.dashboard', 'Coach workspace'],
            'user' => ['member.dashboard', 'Member portal'],
        ] as $role => [$routeName, $portalLabel]) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->get(route($routeName))
                ->assertOk()
                ->assertSee($portalLabel)
                ->assertSee('Change password')
                ->assertDontSee('Account settings');
        }
    }

    public function test_authenticated_user_can_change_their_password(): void
    {
        $user = User::factory()->member()->create();

        $this->actingAs($user)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Current password')
            ->assertSee('New password')
            ->assertDontSee('Preferred message channel')
            ->assertDontSee('Address');

        $this->actingAs($user)->put(route('profile.update'), [
            'current_password' => 'password',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertTrue(Hash::check('new-secure-password', $user->fresh()->password));
    }

    public function test_password_change_requires_the_correct_current_password(): void
    {
        $user = User::factory()->member()->create();

        $this->actingAs($user)->from(route('profile.edit'))->put(route('profile.update'), [
            'current_password' => 'incorrect-password',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertRedirect(route('profile.edit'))->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_member_can_submit_gcash_application_with_private_receipt(): void
    {
        Storage::fake('local');
        Notification::fake([ApprovalRequested::class]);
        $coachUser = User::factory()->coach()->create();
        $user = User::factory()->member()->create();
        $coach = Coach::factory()->for($coachUser)->create(['specialty' => 'Strength']);
        $plan = MembershipPlan::factory()->create(['duration_days' => 30, 'price' => 1200]);

        $response = $this->actingAs($user)->post(route('member.application.store'), [
            'fitness_goal' => 'Improve strength',
            'first_name' => 'Ana',
            'last_name' => 'Reyes',
            'gender' => 'Female',
            'phone_country_code' => '+63',
            'phone' => '09171234567',
            'address' => 'Tagum City',
            'preferred_channel' => 'private_message',
            'availability_days' => ['Monday', 'Wednesday', 'Friday'],
            'availability_starts_at' => '17:00',
            'availability_ends_at' => '19:00',
            'coach_id' => $coach->id,
            'membership_plan_id' => $plan->id,
            'payment_method' => 'gcash',
            'reference' => 'GCASH-12345',
            'receipt' => UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect(route('member.dashboard'))->assertSessionHasNoErrors();

        $member = Member::whereBelongsTo($user)->firstOrFail();
        $transaction = Transaction::whereBelongsTo($member)->firstOrFail();

        $this->assertSame('pending', $member->currentSubscription->status);
        $this->assertSame('Improve strength', $member->fitness_goal);
        $this->assertSame(['Monday', 'Wednesday', 'Friday'], $member->availability_days);
        $this->assertSame('+63 09171234567', $member->phone);
        $this->assertNull($member->birth_date);
        $this->assertSame('GCASH-12345', $transaction->reference);
        Storage::disk('local')->assertExists($transaction->receipt_path);
        Notification::assertSentTo($coachUser, ApprovalRequested::class, fn (ApprovalRequested $notification): bool => $notification->routeName === 'coach.approvals.index');
    }

    public function test_member_can_submit_a_gym_only_application_without_a_coach(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->member()->create();
        $plan = MembershipPlan::factory()->create(['duration_days' => 30, 'price' => 1200]);
        Notification::fake([ApprovalRequested::class]);

        $response = $this->actingAs($user)->post(route('member.application.store'), [
            'fitness_goal' => 'Build muscle',
            'first_name' => 'Marco',
            'last_name' => 'Santos',
            'gender' => 'Male',
            'phone_country_code' => '+63',
            'phone' => '09171234567',
            'address' => 'Tagum City',
            'preferred_channel' => 'private_message',
            'availability_days' => ['Monday'],
            'availability_starts_at' => '17:00',
            'availability_ends_at' => '19:00',
            'membership_plan_id' => $plan->id,
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect(route('member.dashboard'))->assertSessionHasNoErrors();
        $member = Member::whereBelongsTo($user)->firstOrFail();
        $this->assertNull($member->coach_id);
        $this->assertSame('pending', $member->currentSubscription->status);
        Notification::assertSentTo(
            $admin,
            ApprovalRequested::class,
            fn (ApprovalRequested $notification): bool => $notification->routeName === 'admin.approvals.index',
        );
        $this->actingAs($admin)->get(route('admin.members.index'))
            ->assertOk()
            ->assertSee($member->full_name)
            ->assertSee($member->member_code)
            ->assertSee('Pending');
    }

    public function test_member_cannot_select_a_coach_without_a_portal_account(): void
    {
        $user = User::factory()->member()->create();
        $coach = Coach::factory()->create();
        $plan = MembershipPlan::factory()->create();

        $response = $this->actingAs($user)->from(route('member.dashboard'))->post(route('member.application.store'), [
            'fitness_goal' => 'Improve strength',
            'first_name' => 'Ana',
            'last_name' => 'Reyes',
            'gender' => 'Female',
            'phone_country_code' => '+63',
            'phone' => '09171234567',
            'address' => 'Tagum City',
            'preferred_channel' => 'private_message',
            'availability_days' => ['Monday'],
            'availability_starts_at' => '17:00',
            'availability_ends_at' => '19:00',
            'coach_id' => $coach->id,
            'membership_plan_id' => $plan->id,
            'payment_method' => 'cash',
        ]);

        $response
            ->assertRedirect(route('member.dashboard'))
            ->assertSessionHasErrors('coach_id');
        $this->assertDatabaseMissing('members', ['user_id' => $user->id]);
    }

    public function test_membership_application_requires_an_allowed_country_code_and_exactly_eleven_phone_digits(): void
    {
        $user = User::factory()->member()->create();

        $this->actingAs($user)->from(route('member.dashboard'))->post(route('member.application.store'), [
            'phone_country_code' => '+999',
            'phone' => '0917123456',
        ])->assertRedirect(route('member.dashboard'))->assertSessionHasErrors([
            'phone_country_code',
            'phone' => 'Enter a Philippine mobile number with exactly 11 digits starting with 09 (e.g. 09171234567).',
        ]);

        $this->assertDatabaseMissing('members', ['user_id' => $user->id]);
    }

    #[DataProvider('invalidPhilippineMobileNumbers')]
    public function test_invalid_member_mobile_numbers_are_rejected_without_creating_an_application(mixed $phone): void
    {
        Notification::fake([ApprovalRequested::class]);
        $user = User::factory()->member()->create();
        $plan = MembershipPlan::factory()->create();

        $this->actingAs($user)->from(route('member.dashboard'))->post(route('member.application.store'), [
            'fitness_goal' => 'Build muscle',
            'first_name' => 'Marco',
            'last_name' => 'Santos',
            'gender' => 'Male',
            'phone_country_code' => '+63',
            'phone' => $phone,
            'address' => 'Tagum City',
            'preferred_channel' => 'private_message',
            'availability_days' => ['Monday'],
            'availability_starts_at' => '17:00',
            'availability_ends_at' => '19:00',
            'membership_plan_id' => $plan->id,
            'payment_method' => 'cash',
        ])->assertRedirect(route('member.dashboard'))->assertSessionHasErrors([
            'phone' => 'Enter a Philippine mobile number with exactly 11 digits starting with 09 (e.g. 09171234567).',
        ]);

        $this->assertDatabaseCount('members', 0);
        $this->assertDatabaseCount('subscriptions', 0);
        $this->assertDatabaseCount('transactions', 0);
        Notification::assertNothingSent();
    }

    /** @return array<string, array{mixed}> */
    public static function invalidPhilippineMobileNumbers(): array
    {
        return [
            'letters' => ['09abcdefghi'],
            'embedded letter' => ['0917a1234567'],
            'too short' => ['0917123456'],
            'too long' => ['091712345678'],
            'wrong prefix' => ['08171234567'],
            'all zeroes' => ['00000000000'],
            'international format' => ['+639171234567'],
            'embedded spaces' => ['0917 1234567'],
            'symbols' => ['0917-1234567'],
            'empty' => [''],
            'array' => [['09171234567']],
        ];
    }

    public function test_membership_application_rejects_a_non_philippine_country_code(): void
    {
        $this->actingAs(User::factory()->member()->create())->post(route('member.application.store'), [
            'phone_country_code' => '+1',
            'phone' => '09171234567',
        ])->assertSessionHasErrors([
            'phone_country_code' => 'Only Philippine mobile numbers (+63) are accepted.',
        ]);

        $this->assertDatabaseCount('members', 0);
    }

    public function test_coach_can_only_open_and_record_own_clients(): void
    {
        $coachUser = User::factory()->coach()->create();
        $coach = Coach::factory()->for($coachUser)->create();
        $ownClient = Member::factory()->for($coach)->create();
        $otherClient = Member::factory()->create();
        Subscription::factory()->for($ownClient)->create(['status' => 'active']);
        Subscription::factory()->for($otherClient)->create(['status' => 'active']);

        $this->actingAs($coachUser)->get(route('coach.clients.show', $otherClient))->assertNotFound();

        $this->actingAs($coachUser)->post(route('coach.clients.records.store', $ownClient), [
            'title' => 'Initial assessment',
            'notes' => 'Baseline mobility and strength assessment completed.',
            'recorded_at' => today()->toDateString(),
        ])->assertRedirect();

        $this->assertDatabaseHas('coach_client_records', [
            'coach_id' => $coach->id,
            'member_id' => $ownClient->id,
            'title' => 'Initial assessment',
        ]);
    }

    public function test_coach_client_roster_only_contains_approved_members(): void
    {
        $coachUser = User::factory()->coach()->create();
        $coach = Coach::factory()->for($coachUser)->create();
        $approvedMember = Member::factory()->for($coach)->create(['first_name' => 'Approved', 'last_name' => 'Client']);
        $pendingMember = Member::factory()->for($coach)->create(['first_name' => 'Pending', 'last_name' => 'Applicant']);
        $declinedMember = Member::factory()->for($coach)->create(['first_name' => 'Declined', 'last_name' => 'Applicant']);
        Subscription::factory()->for($approvedMember)->create(['status' => 'active']);
        Subscription::factory()->for($pendingMember)->create(['status' => 'pending']);
        Subscription::factory()->for($declinedMember)->create(['status' => 'declined']);

        $this->actingAs($coachUser)->get(route('coach.clients.index'))
            ->assertOk()
            ->assertSeeText('Approved Client')
            ->assertDontSeeText('Pending Applicant')
            ->assertDontSeeText('Declined Applicant');

        $this->actingAs($coachUser)->get(route('coach.clients.show', $pendingMember))->assertNotFound();
        $this->actingAs($coachUser)->post(route('coach.clients.records.store', $pendingMember), [
            'title' => 'Should not be stored',
            'notes' => 'A pending applicant must not receive a client record.',
            'recorded_at' => today()->toDateString(),
        ])->assertNotFound();

        $this->assertDatabaseMissing('coach_client_records', ['member_id' => $pendingMember->id]);
    }

    #[DataProvider('messageRolePairs')]
    public function test_allowed_role_pair_can_exchange_private_messages(string $senderRole, string $recipientRole): void
    {
        Notification::fake([NewMessageReceived::class]);
        $sender = User::factory()->create(['role' => $senderRole]);
        $recipient = User::factory()->create(['role' => $recipientRole]);

        if ($sender->isCoach()) {
            Message::factory()->for($recipient, 'sender')->for($sender, 'recipient')->create();
        }

        $this->actingAs($sender)->get(route('chat.index'))->assertSee($recipient->name);

        $this->actingAs($sender)->post(route('chat.store', $recipient), ['body' => 'Can I move my session?'])
            ->assertRedirect(route('chat.show', $recipient));

        $this->assertDatabaseHas('messages', [
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'body' => 'Can I move my session?',
        ]);
        Notification::assertSentTo($recipient, NewMessageReceived::class);
    }

    /** @return array<string, array{string, string}> */
    public static function messageRolePairs(): array
    {
        return [
            'member to coach' => ['user', 'coach'],
            'member to admin' => ['user', 'admin'],
            'coach to member' => ['coach', 'user'],
            'coach to coach' => ['coach', 'coach'],
            'admin to coach' => ['admin', 'coach'],
            'admin to member' => ['admin', 'user'],
        ];
    }

    public function test_member_chat_only_lists_staff_and_blocks_other_members(): void
    {
        Notification::fake([NewMessageReceived::class]);
        $member = User::factory()->member()->create(['name' => 'Current Member']);
        $otherMember = User::factory()->member()->create(['name' => 'Other Member']);
        $coach = User::factory()->coach()->create(['name' => 'Coach Contact']);
        $admin = User::factory()->create(['name' => 'Admin Contact']);

        $this->actingAs($member)->get(route('chat.index'))
            ->assertOk()
            ->assertSeeText($coach->name)
            ->assertSeeText($admin->name)
            ->assertDontSeeText($otherMember->name);

        $this->actingAs($member)->get(route('chat.show', $otherMember))->assertForbidden();
        $this->actingAs($member)->post(route('chat.store', $otherMember), [
            'body' => 'This message must not be sent.',
        ])->assertForbidden();

        $this->assertDatabaseMissing('messages', [
            'sender_id' => $member->id,
            'recipient_id' => $otherMember->id,
        ]);
        Notification::assertNothingSent();
    }

    public function test_coach_chat_only_allows_assigned_clients_admins_and_direct_senders(): void
    {
        Notification::fake([NewMessageReceived::class]);
        $coachUser = User::factory()->coach()->create();
        $coach = Coach::factory()->for($coachUser, 'user')->create();
        $assignedClient = User::factory()->member()->create(['name' => 'Assigned Chat Client']);
        Member::factory()->for($assignedClient, 'user')->for($coach)->create();
        $admin = User::factory()->create(['name' => 'Chat Administrator']);
        $directSender = User::factory()->member()->create(['name' => 'Direct Chat Sender']);
        $incomingMessage = Message::factory()->for($directSender, 'sender')->for($coachUser, 'recipient')->create();
        $unrelatedMember = User::factory()->member()->create(['name' => 'Unrelated Chat Member']);
        Member::factory()->for($unrelatedMember, 'user')->create();
        $unrelatedCoach = User::factory()->coach()->create(['name' => 'Unrelated Chat Coach']);
        Message::factory()->for($coachUser, 'sender')->for($unrelatedMember, 'recipient')->create();
        Message::factory()->for($unrelatedCoach, 'sender')->for($admin, 'recipient')->create();

        $this->actingAs($coachUser)->get(route('chat.index'))
            ->assertSeeText($assignedClient->name)
            ->assertSeeText($admin->name)
            ->assertSeeText($directSender->name)
            ->assertDontSeeText($unrelatedMember->name)
            ->assertDontSeeText($unrelatedCoach->name);

        foreach ([$assignedClient, $admin, $directSender] as $contact) {
            $this->get(route('chat.show', $contact))->assertOk();
            $this->post(route('chat.store', $contact), ['body' => 'Allowed coach message'])
                ->assertRedirect(route('chat.show', $contact));
            $this->assertDatabaseHas('messages', [
                'sender_id' => $coachUser->id,
                'recipient_id' => $contact->id,
                'body' => 'Allowed coach message',
            ]);
            Notification::assertSentTo($contact, NewMessageReceived::class);
        }

        $this->assertNotNull($incomingMessage->fresh()->read_at);
        $this->get(route('chat.show', $directSender))->assertSeeText($incomingMessage->body);

        foreach ([$unrelatedMember, $unrelatedCoach, $coachUser] as $contact) {
            $this->get(route('chat.show', $contact))->assertForbidden();
            $this->post(route('chat.store', $contact), ['body' => 'Blocked coach message'])->assertForbidden();
        }

        $this->assertDatabaseMissing('messages', ['body' => 'Blocked coach message']);
        Notification::assertNotSentTo([$unrelatedMember, $unrelatedCoach, $coachUser], NewMessageReceived::class);
    }

    public function test_coach_without_roster_record_can_reply_to_direct_senders_but_cannot_access_unassigned_clients(): void
    {
        $coachUser = User::factory()->coach()->create();
        $unassignedUser = User::factory()->member()->create(['name' => 'Unassigned Chat Client']);
        Member::factory()->for($unassignedUser, 'user')->create(['coach_id' => null]);
        $directSender = User::factory()->coach()->create(['name' => 'Incoming Coach Contact']);
        Message::factory()->for($directSender, 'sender')->for($coachUser, 'recipient')->create(['read_at' => now()]);

        $this->actingAs($coachUser)->get(route('chat.index'))
            ->assertSeeText($directSender->name)
            ->assertDontSeeText($unassignedUser->name);
        $this->get(route('chat.show', $directSender))->assertOk();
        $this->get(route('chat.show', $unassignedUser))->assertForbidden();
        $this->post(route('chat.store', $unassignedUser), ['body' => 'Not allowed'])->assertForbidden();
        $this->assertDatabaseMissing('messages', ['sender_id' => $coachUser->id]);
    }

    public function test_coach_loses_chat_access_when_a_client_is_reassigned_without_an_incoming_message(): void
    {
        $coachUser = User::factory()->coach()->create();
        $coach = Coach::factory()->for($coachUser, 'user')->create();
        $clientUser = User::factory()->member()->create(['name' => 'Reassigned Chat Client']);
        $client = Member::factory()->for($clientUser, 'user')->for($coach)->create();
        Message::factory()->for($coachUser, 'sender')->for($clientUser, 'recipient')->create();
        $client->update(['coach_id' => Coach::factory()->create()->id]);

        $this->actingAs($coachUser)->get(route('chat.index'))->assertDontSeeText($clientUser->name);
        $this->get(route('chat.show', $clientUser))->assertForbidden();
        $this->post(route('chat.store', $clientUser), ['body' => 'After reassignment'])->assertForbidden();
        $this->assertDatabaseMissing('messages', ['body' => 'After reassignment']);
    }

    public function test_admin_sidebar_omits_users_and_calendar_and_calendar_route_is_forbidden(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertDontSee('Users')
            ->assertDontSee('Calendar');
        $this->actingAs($admin)->get(route('calendar.index'))->assertForbidden();
    }

    public function test_database_seeder_creates_one_account_for_each_portal_role(): void
    {
        $this->seed();

        $this->assertDatabaseCount('users', 3);
        $this->assertDatabaseHas('users', ['email' => 'admin@rushfitness.test', 'role' => 'admin']);
        $this->assertDatabaseHas('users', ['email' => 'coach@rushfitness.test', 'role' => 'coach']);
        $this->assertDatabaseHas('users', ['email' => 'member@rushfitness.test', 'role' => 'user']);
        $this->assertSame(
            User::where('email', 'coach@rushfitness.test')->value('id'),
            Coach::where('name', 'Alex Rivera')->value('user_id'),
        );
    }

    public function test_member_transaction_history_excludes_other_members_records(): void
    {
        $user = User::factory()->member()->create();
        $ownMember = Member::factory()->for($user)->create();
        $ownTransaction = Transaction::factory()->for($ownMember)->create(['reference' => 'OWN-REFERENCE']);
        $otherTransaction = Transaction::factory()->create(['reference' => 'PRIVATE-OTHER-REFERENCE']);

        $this->actingAs($user)->get(route('member.transactions.index'))
            ->assertSee($ownTransaction->reference)
            ->assertDontSee($otherTransaction->reference);
    }

    public function test_admin_can_create_and_link_coach_portal_account(): void
    {
        $admin = User::factory()->create();
        $coach = Coach::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Coach Jordan',
            'email' => 'jordan@example.test',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
            'role' => 'coach',
            'phone' => '09170000000',
            'preferred_channel' => 'private_message',
            'coach_id' => $coach->id,
        ]);

        $account = User::where('email', 'jordan@example.test')->firstOrFail();

        $response->assertRedirect(route('admin.users.edit', $account));
        $this->assertSame($account->id, $coach->fresh()->user_id);
        $this->assertSame('coach', $account->role);
    }
}
