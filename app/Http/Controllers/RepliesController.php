<?php

namespace App\Http\Controllers;

use App\Models\Thread;
use Illuminate\Http\Request;

class RepliesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Thread $thread, Request $request)
    {
        $request->validate([
            'body' => ['required', 'string']
        ]);

        $reply = $thread->addReply([
            'body' => $request->body,
            'user_id' => auth()->id()
        ]);

        return response()->json($reply, 201);
    }
}
