<?php

namespace App\Http\Controllers;

use App\Filters\ThreadFilters;
use App\Models\Channel;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ThreadsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index(Request $request, ThreadFilters $filters)
    {
        $popular = $request->input('popular');
        $threads = Thread::with('channel')->withCount('replies')->filter($filters);

        if ($popular !== null) {
            if ($popular === 'true' || $popular === 'false' || $popular === true || $popular === false) {
                $threads = $threads->get();
            } else {
                $threads = $threads->latest()->get();
            }
        } else {
            $threads = $threads->latest()->get();
        }

        return view('threads.index', compact('threads'));
    }

    public function create()
    {
        $channels = Channel::all();
        return view('threads.create', compact('channels'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string'],
            'body' => ['required', 'string'],
            'channel_id' => ['required', 'exists:channels,id'],
        ]);

        $thread = Thread::create([
            'title' => $request->title,
            'body' => $request->body,
            'user_id' => Auth::id(),
            'channel_id' => $request->channel_id
        ]);

        return response()->json($thread, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Thread  $thread
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function show($channel, Thread $thread)
    {
        $thread->load(['channel', 'replies.user'])->loadCount('replies');
        return view('threads.show', compact('thread'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Thread  $thread
     * @return \Illuminate\Http\Response
     */
    public function edit(Thread $thread)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Thread  $thread
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Thread $thread)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Thread  $thread
     * @return \Illuminate\Http\Response
     */
    public function destroy(Thread $thread)
    {
        //
    }
}
