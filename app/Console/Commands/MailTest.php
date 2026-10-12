<?php

namespace App\Console\Commands;

use App\Notifications\DeploymentMailTest;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

#[Signature('app:mail-test {email : Address that should receive the delivery test}')]
#[Description('Send a test email through the configured production mail transport')]
class MailTest extends Command
{
    public function handle(): int
    {
        $email = trim((string) $this->argument('email'));

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $this->components->error('Enter a valid email address.');

            return self::FAILURE;
        }

        Notification::route('mail', $email)->notify(new DeploymentMailTest);
        $this->components->info("Delivery test sent to {$email}.");

        return self::SUCCESS;
    }
}
