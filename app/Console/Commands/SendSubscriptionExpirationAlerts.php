<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionExpiresSoon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

#[Signature('subscriptions:send-expiration-alerts')]
#[Description('Notify administrators about subscriptions expiring in exactly three days')]
class SendSubscriptionExpirationAlerts extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        Subscription::where('status', 'active')
            ->whereDate('expires_at', '<', today())
            ->update(['status' => 'expired']);

        $administrators = User::where('role', 'admin')->get();

        if ($administrators->isEmpty()) {
            $this->warn('No administrator accounts are available to receive alerts.');

            return self::FAILURE;
        }

        $sentCount = 0;

        Subscription::query()
            ->with(['member', 'plan'])
            ->where('status', 'active')
            ->whereDate('expires_at', today()->addDays(3))
            ->whereNull('reminder_sent_at')
            ->chunkById(100, function ($subscriptions) use ($administrators, &$sentCount): void {
                foreach ($subscriptions as $subscription) {
                    Notification::send($administrators, new SubscriptionExpiresSoon($subscription));
                    $subscription->update(['reminder_sent_at' => now()]);
                    $sentCount++;
                }
            });

        $this->info("Sent {$sentCount} expiration alert(s).");

        return self::SUCCESS;
    }
}
