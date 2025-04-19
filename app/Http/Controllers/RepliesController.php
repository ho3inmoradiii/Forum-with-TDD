<?php

namespace App\Http\Controllers;

use App\Models\Thread;
use Illuminate\Http\Request;

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
        return response()->json([
            'reply' => $reply,
            'message' => 'Reply added successfully'
        ], 201);
    }
}
