<?php

namespace App\Filters;

use App\Models\Channel;
use App\Models\User;

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
        $popularStatusBoolean = filter_var($popularStatus, FILTER_VALIDATE_BOOLEAN);
        if ($popularStatusBoolean === true) {
            return $this->builder->orderBy('replies_count', 'desc');
        } elseif ($popularStatusBoolean === false) {
            return $this->builder->orderBy('replies_count', 'asc');
        }
        return $this->builder;
    }
}
