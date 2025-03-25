<?php

namespace App\Http\Controllers;

use App\Models\Thread;
use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(User $user)
    {
        $user->load('threads.channel');
        return view('profiles.show', compact('user'));
    }
}
