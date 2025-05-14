<?php

namespace App\Filters;

use App\Models\Channel;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ThreadFilters extends ThreadFilter
{
    protected function by($username)
    {
        $user = User::where('name', $username)->first();

        if ($user) {
            return $this->builder->where('user_id', $user->id);
        }

        session()->flash('message', "User '{$username}' not found. Showing all threads.");
        return $this->builder;
    }

    protected function channel($channelName)
    {
        $channel = Channel::where('name', $channelName)->first();
        if ($channel) {
            return $this->builder->where('channel_id', $channel->id);
        }
        session()->flash('message', "Channel '{$channelName}' not found. Showing all threads.");
        return $this->builder;
    }

    public function popular($popularStatus)
    {
        $this->builder->getQuery()->orders = [];
        if ($popularStatus === 'true' || $popularStatus === true) {
            return $this->builder->orderBy('replies_count', 'desc');
        } elseif ($popularStatus === 'false' || $popularStatus === false) {
            return $this->builder->orderBy('replies_count', 'asc');
        }
        return $this->builder;
    }

    public function unanswered($unansweredStatus)
    {
        if ($unansweredStatus === true || $unansweredStatus === 'true' || $unansweredStatus === '1') {
            return $this->builder->whereDoesntHave('replies');
        } elseif ($unansweredStatus === false || $unansweredStatus === 'false' || $unansweredStatus === '0') {
            return $this->builder->whereHas('replies');
        }
        return $this->builder;
    }
}
