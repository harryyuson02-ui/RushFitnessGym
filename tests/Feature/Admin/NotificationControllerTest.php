<?php

namespace Tests\Feature\Admin;

use App\Models\Message;
use App\Models\User;
use App\Notifications\ApprovalDecision;
use App\Notifications\ApprovalRequested;
use App\Notifications\NewMessageReceived;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[DataProvider('portalRoles')]
    public function test_clicking_message_notification_opens_sender_conversation_and_marks_it_read(string $role): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create(['role' => $role]);
        $message = Message::factory()->for($sender, 'sender')->for($recipient, 'recipient')->create();
        $recipient->notify(new NewMessageReceived($message->load('sender')));
        $notification = $recipient->unreadNotifications()->firstOrFail();

        $response = $this->actingAs($recipient)->patch(route('notifications.update', $notification));

        $response->assertRedirect(route('chat.show', $sender));
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_clicking_non_message_notification_opens_chat_and_marks_it_read(): void
    {
        $recipient = User::factory()->member()->create();
        $recipient->notify(new ApprovalDecision('Application approved', 'Your membership is active.'));
        $notification = $recipient->unreadNotifications()->firstOrFail();

        $response = $this->actingAs($recipient)->patch(route('notifications.update', $notification));

        $response->assertRedirect(route('chat.index'));
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_clicking_approval_request_opens_coach_approval_queue_and_marks_it_read(): void
    {
        $coach = User::factory()->coach()->create();
        $coach->notify(new ApprovalRequested(
            'New membership application',
            'A member selected you as their coach.',
            'coach.approvals.index',
        ));
        $notification = $coach->unreadNotifications()->firstOrFail();

        $response = $this->actingAs($coach)->patch(route('notifications.update', $notification));

        $response->assertRedirect(route('coach.approvals.index'));
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_notification_dropdown_renders_a_clickable_chat_action_and_escapes_sender_name(): void
    {
        $sender = User::factory()->create(['name' => '<script>alert("sender")</script>']);
        $recipient = User::factory()->member()->create();
        $message = Message::factory()->for($sender, 'sender')->for($recipient, 'recipient')->create();
        $recipient->notify(new NewMessageReceived($message->load('sender')));
        $notification = $recipient->unreadNotifications()->firstOrFail();

        $response = $this->actingAs($recipient)->get(route('member.dashboard'));

        $response
            ->assertSee(route('notifications.update', $notification), false)
            ->assertSeeText('Open conversation')
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert("sender")</script>', false);
    }

    public function test_user_cannot_open_another_users_notification(): void
    {
        $owner = User::factory()->member()->create();
        $otherUser = User::factory()->member()->create();
        $owner->notify(new ApprovalDecision('Private update', 'Only the owner can open this.'));
        $notification = $owner->unreadNotifications()->firstOrFail();

        $this->actingAs($otherUser)->patch(route('notifications.update', $notification))->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }

    /** @return array<string, array{string}> */
    public static function portalRoles(): array
    {
        return [
            'admin' => ['admin'],
            'coach' => ['coach'],
            'member' => ['user'],
        ];
    }
}
