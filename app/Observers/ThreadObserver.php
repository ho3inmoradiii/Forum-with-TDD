<?php

namespace App\Observers;

use App\Models\Activity;
use App\Models\Thread;
use Illuminate\Support\Facades\Log;
use App\Constants\ActivityTypes;

class ThreadObserver
{
    /**
     * Handle the Thread "created" event.
     *
     * @param  \App\Models\Thread  $thread
     * @return void
     */
    public function created(Thread $thread)
    {
        try {
            $thread->activities()->create([
                'user_id' => $thread->user_id,
                'activity_type' => ActivityTypes::THREAD_CREATED,
                'target_id' => $thread->id,
                'target_type' => 'App\Models\Thread',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record activity for thread creation: ' . $e->getMessage());
        }
    }

    /**
     * Handle the Thread "updated" event.
     *
     * @param  \App\Models\Thread  $thread
     * @return void
     */
    public function updated(Thread $thread)
    {
        //
    }

    /**
     * Handle the Thread "deleted" event.
     *
     * @param  \App\Models\Thread  $thread
     * @return void
     */
    public function deleted(Thread $thread)
    {
        //
    }

    /**
     * Handle the Thread "restored" event.
     *
     * @param  \App\Models\Thread  $thread
     * @return void
     */
    public function restored(Thread $thread)
    {
        //
    }

    /**
     * Handle the Thread "force deleted" event.
     *
     * @param  \App\Models\Thread  $thread
     * @return void
     */
    public function forceDeleted(Thread $thread)
    {
        //
    }
}
