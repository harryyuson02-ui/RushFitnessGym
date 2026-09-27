<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionExpiresSoon;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SubscriptionExpirationAlertTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_command_notifies_admin_exactly_three_days_before_expiration_once(): void
    {
        $this->travelTo('2026-09-28 08:00:00');
        $admin = User::factory()->create();
        $dueSubscription = Subscription::factory()->create(['expires_at' => '2026-10-01']);
        Subscription::factory()->create(['expires_at' => '2026-10-02']);
        Notification::fake([SubscriptionExpiresSoon::class]);

        $this->artisan('subscriptions:send-expiration-alerts')->assertSuccessful();

        Notification::assertSentTo($admin, SubscriptionExpiresSoon::class, fn ($notification): bool => $notification->subscription->is($dueSubscription));
        $this->assertNotNull($dueSubscription->fresh()->reminder_sent_at);

        $this->artisan('subscriptions:send-expiration-alerts')->assertSuccessful();
        Notification::assertSentToTimes($admin, SubscriptionExpiresSoon::class, 1);
        $this->travelBack();
    }
}
