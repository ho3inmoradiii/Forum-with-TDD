<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewReplyNotification extends Notification
{
    use Queueable;

    protected $thread;
    protected $reply;

    public function __construct($thread, $reply)
    {
        $this->thread = $thread;
        $this->reply = $reply;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'thread_id' => $this->thread->id,
            'channel_name' => $this->thread->channel->name,
            'thread_title' => $this->thread->title,
            'reply_id' => $this->reply->id,
            'reply_user' => $this->reply->user->name,
            'message' => " New reply added to '{$this->thread->title}' ",
            'created_at' => now(),
        ];
    }
}
