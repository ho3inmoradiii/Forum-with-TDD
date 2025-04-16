<?php

namespace App\Observers;

use App\Models\Reply;
use App\Constants\ActivityTypes;
use Illuminate\Support\Facades\Log;

class ReplyObserver
{
    /**
     * Handle the Reply "created" event.
     *
     * @param  \App\Models\Reply  $reply
     * @return void
     */
    public function created(Reply $reply)
    {
        try {
            $reply->activities()->create([
                'user_id' => $reply->user_id,
                'activity_type' => ActivityTypes::REPLY_ADDED,
                'target_id' => $reply->id,
                'target_type' => 'App\Models\Reply',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record activity for reply creation: Reply ID ' . $reply->id . ', User ID ' . $reply->user_id . ', Error: ' . $e->getMessage());
        }
    }

    /**
     * Handle the Reply "updated" event.
     *
     * @param  \App\Models\Reply  $reply
     * @return void
     */
    public function updated(Reply $reply)
    {
        //
    }

    /**
     * Handle the Reply "deleted" event.
     *
     * @param  \App\Models\Reply  $reply
     * @return void
     */
    public function deleted(Reply $reply)
    {
        try {
            $reply->activities()->delete();
        } catch (\Exception $e) {
            Log::error('Failed to destroy activity for reply deletion: Reply ID ' . $reply->id . ', User ID ' . $reply->user_id . ', Error: ' . $e->getMessage());
        }
    }

    /**
     * Handle the Reply "restored" event.
     *
     * @param  \App\Models\Reply  $reply
     * @return void
     */
    public function restored(Reply $reply)
    {
        //
    }

    /**
     * Handle the Reply "force deleted" event.
     *
     * @param  \App\Models\Reply  $reply
     * @return void
     */
    public function forceDeleted(Reply $reply)
    {
        //
    }
}
