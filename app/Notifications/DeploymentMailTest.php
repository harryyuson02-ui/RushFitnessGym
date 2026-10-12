<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeploymentMailTest extends Notification
{
    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Rush Fitness Gym email delivery test')
            ->greeting('Email delivery is working')
            ->line('Rush Fitness Gym successfully delivered this message through the configured mail provider.')
            ->line('Password reset codes can now be delivered to administrator, coach, and member accounts.');
    }
}
