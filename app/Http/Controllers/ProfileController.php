<?php

namespace App\Http\Controllers;

use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(User $user)
    {
        $user->load([
            'activities' => function ($query) {
                $query->orderBy('created_at', 'DESC');
            },
            'activities.target'
        ]);

        $threadIds = $user->activities->where('target_type', 'App\Models\Thread')->pluck('target_id')->toArray();
        if (!empty($threadIds)) {
            $threads = \App\Models\Thread::whereIn('id', $threadIds)
                ->with('channel')
                ->get()
                ->keyBy('id');

            $user->activities->each(function ($activity) use ($threads) {
                if ($activity->target_type === 'App\Models\Thread') {
                    $activity->setRelation('target', $threads[$activity->target_id]);
                }
            });
        }

        $replyIds = $user->activities->where('target_type', 'App\Models\Reply')->pluck('target_id')->toArray();
        if (!empty($replyIds)) {
            $replies = \App\Models\Reply::whereIn('id', $replyIds)
                ->with('thread')
                ->get()
                ->keyBy('id');

            $user->activities->each(function ($activity) use ($replies) {
                if ($activity->target_type === 'App\Models\Reply') {
                    $activity->setRelation('target', $replies[$activity->target_id]);
                }
            });
        }

        return view('profiles.show', compact('user'));
    }
}
