<?php

namespace App\Http\Controllers;

use App\Constants\ActivityTypes;
use App\Models\Thread;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubscribeController extends Controller
{
    public function store(Thread $thread)
    {
        try {
            // Check if the thread is subscribed
            if (Auth::user()->subscribedThreads()->where('thread_id', $thread->id)->exists()) {
                return response()->json(['message' => 'Thread already subscribed'], 422);
            }

            // Attach the reply to the user's favorites
            Auth::user()->subscribedThreads()->attach($thread->id);

//            // Record the activity
//            $thread->activities()->create([
//                'user_id' => Auth::id(),
//                'activity_type' => ActivityTypes::REPLY_FAVORITED,
//                'target_id' => $reply->id,
//                'target_type' => 'App\Models\Reply',
//            ]);

            return response()->json(['message' => 'Thread Subscribed'], 201);
        } catch (\Exception $e) {
            Log::error('Failed to subscribe thread: Thread ID ' . $thread->id . ', User ID ' . Auth::id() . ', Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to subscribe reply'], 500);
        }
    }
}
