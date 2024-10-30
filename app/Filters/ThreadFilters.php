<?php

namespace App\Filters;

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

    // Add more filter methods here as needed
}
