<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Reply;
use App\Models\Thread;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RepliesController extends Controller
{
    public function index(Channel $channel, Thread $thread, Request $request)
    {
        $replies = Reply::with([
            'user',
            'favoritedBy' => function ($query) {
                $query->where('user_id', auth()->id());
            }
        ])->where('thread_id', $thread->id)
            ->latest()
            ->paginate($request->per_page);

        $modifiedReplies = $replies->getCollection()->map(function ($reply) {
            $reply->is_favorited = $reply->favoritedBy->isNotEmpty();
            return $reply;
        });

        $replies->setCollection($modifiedReplies);
        return $replies;
    }

    public function store($channel, Thread $thread, Request $request)
    {
        $request->validate([
            'body' => ['required', 'string']
        ], [
            'body.required' => 'Please provide a valid reply body.',
            'body.string' => 'The reply body must be a valid string.'
        ]);

        try {
            $reply = $thread->addReply([
                'body' => $request->body,
                'user_id' => auth()->id()
            ]);

            $reply->load('user');
            return response()->json($reply, 201);
        } catch (\Exception $e) {
            Log::error('Failed to add reply for thread: Thread ID ' . $thread->id . ', Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to add reply'], 500);
        }
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

    /**
     * Update the specified reply.
     *
     * @param Request $request
     * @param Reply $reply
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, Reply $reply)
    {
        $this->authorize('update', $reply);

        $request->validate([
            'body' => ['required', 'string']
        ], [
            'body.required' => 'Please provide a valid reply body.',
            'body.string' => 'The reply body must be a valid string.'
        ]);

        try {
            $reply->update(['body' => $request->body]);
            $reply->load('user');
            return response()->json($reply, 200);
        } catch (\Exception $e) {
            Log::error('Failed to update reply: Reply ID ' . $reply->id . ', Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update reply'], 500);
        }
    }
}
