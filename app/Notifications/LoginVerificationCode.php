<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginVerificationCode extends Notification
{
    public function __construct(public string $code) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Rush Fitness Gym sign-in code')
            ->greeting('Confirm your sign-in')
            ->line('Use this verification code to sign in to your Rush Fitness Gym account:')
            ->line($this->code)
            ->line('This code expires in 10 minutes. Do not share it with anyone.')
            ->line('If you did not request this code, you can safely ignore this email.');
    }
}
