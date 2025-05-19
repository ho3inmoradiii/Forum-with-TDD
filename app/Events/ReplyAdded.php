<?php

namespace App\Events;

use App\Models\Reply;
use App\Models\Thread;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReplyAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $thread;
    public $reply;

    public function __construct(Thread $thread, Reply $reply)
    {
        $this->thread = $thread;
        $this->reply = $reply;
    }
}
