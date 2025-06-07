<?php

namespace App\Listeners;

use App\Events\ReplyAdded;
use App\Notifications\NewReplyNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendNewReplyNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param ReplyAdded $event
     * @return void
     */
    public function handle(ReplyAdded $event)
    {
        $subscribersQuery = $event->thread->subscribers();

        $subscribers = $subscribersQuery
            ->where('users.id', '!=', $event->reply->user_id)
            ->get();

        Notification::send($subscribers, new NewReplyNotification($event->thread, $event->reply));
    }
}
