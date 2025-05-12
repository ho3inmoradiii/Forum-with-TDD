<?php

namespace App\Http\Controllers;

use App\Filters\ThreadFilters;
use App\Models\Channel;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        dump($threads->count());

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

        return response()->json([
            'thread' => $thread,
            'message' => 'Thread created successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Thread  $thread
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function show($channel, Thread $thread)
    {
        $thread->load([
            'channel',
        ]);
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
     * Delete the specified thread and its associated activities.
     *
     * @param  \App\Models\Thread  $thread
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Thread $thread)
    {
        $this->authorize('delete', $thread);

        try {
            DB::transaction(function () use ($thread) {
                // Delete reply activities before cascade deletion
                DB::table('activities')
                    ->where('target_type', 'App\Models\Reply')
                    ->whereIn('target_id', DB::table('replies')->where('thread_id', $thread->id)->pluck('id'))
                    ->delete();

                // Delete thread activities
                $thread->activities()->delete();

                // Delete the thread (will trigger cascade deletion of replies)
                $thread->delete();

                // Log for debugging
                Log::info('Thread and related activities deleted successfully: Thread ID ' . $thread->id);
            });

            return response()->json(['message' => 'Thread deleted successfully.'], 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete thread and related activities: Thread ID ' . $thread->id . ', Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete thread. Please try again.'], 500);
        }
    }
}
