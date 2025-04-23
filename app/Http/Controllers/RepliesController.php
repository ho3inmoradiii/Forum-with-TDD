<?php

namespace App\Http\Controllers;

use App\Models\Reply;
use App\Models\Thread;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RepliesController extends Controller
{
    public function store($channel, Thread $thread, Request $request)
    {
        $request->validate([
            'body' => ['required', 'string']
        ]);

        $reply = $thread->addReply([
            'body' => $request->body,
            'user_id' => auth()->id()
        ]);

        $reply->load('user');
        return response()->json($reply, 201);
    }

    /**
     * Delete the specified thread and its associated activities.
     *
     * @param \App\Models\Reply $reply
     * @return \Illuminate\Http\JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(Reply $reply)
    {
        $this->authorize('delete', $reply);

        try {
            DB::transaction(function () use ($reply) {
                // Delete the reply (will trigger cascade deletion of favorites)
                $reply->delete();

                // Log for debugging
                Log::info('Reply and related activities deleted successfully: Reply ID ' . $reply->id);
            });

            return response()->json(['message' => 'Reply deleted successfully.'], 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete reply and related activities: Reply ID ' . $reply->id . ', Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete reply. Please try again.'], 500);
        }
    }
}
