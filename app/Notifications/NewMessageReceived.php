<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewMessageReceived extends Notification
{
    use Queueable;

    public function __construct(public Message $message) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New message from '.$this->message->sender->name,
            'body' => filled($this->message->body)
                ? str($this->message->body)->limit(80)->toString()
                : 'Sent a photo.',
            'url' => route('chat.show', $this->message->sender),
            'sender_id' => $this->message->sender_id,
        ];
    }
}
