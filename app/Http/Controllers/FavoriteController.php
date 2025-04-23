<?php

namespace App\Http\Controllers;

use App\Constants\ActivityTypes;
use App\Models\Activity;
use App\Models\Reply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FavoriteController extends Controller
{
    public function store(Request $request, Reply $reply)
    {
        try {
            // Check if the reply is already favorited
            if (Auth::user()->favoriteReplies()->where('reply_id', $reply->id)->exists()) {
                return response()->json(['message' => 'Reply already favorited'], 422);
            }

            // Attach the reply to the user's favorites
            Auth::user()->favoriteReplies()->attach($reply->id);

            // Record the activity
            $reply->activities()->create([
                'user_id' => Auth::id(),
                'activity_type' => ActivityTypes::REPLY_FAVORITED,
                'target_id' => $reply->id,
                'target_type' => 'App\Models\Reply',
            ]);

            return response()->json(['message' => 'Reply favorited'], 201);
        } catch (\Exception $e) {
            Log::error('Failed to favorite reply: Reply ID ' . $reply->id . ', User ID ' . Auth::id() . ', Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to favorite reply'], 500);
        }
    }

    public function delete(Reply $reply)
    {
        try {
            // Check if the reply is already favorited
            if (!Auth::user()->favoriteReplies()->where('reply_id', $reply->id)->exists()) {
                return response()->json(['message' => 'Reply was not favorited'], 422);
            }

            DB::transaction(function () use ($reply) {
                // Attach the reply to the user's favorites
                Auth::user()->favoriteReplies()->detach($reply->id);

                $this->deleteActivity($reply);
            });

            return response()->json(['message' => 'Reply Favorite deleted successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete favorite reply: Reply ID ' . $reply->id . ', User ID ' . Auth::id() . ', Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete favorite reply'], 500);
        }
    }

    protected function deleteActivity($reply)
    {
        try {
            $reply->activities()->where([
                'activity_type' => ActivityTypes::REPLY_FAVORITED,
                'user_id' => Auth::id()
            ])->delete();
        } catch (\Exception $e) {
            Log::error('Failed to delete activity for: Reply ID ' . $reply->id . ', Error: ' . $e->getMessage());
        }
    }
}
