<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use Illuminate\Http\Request;

class ChannelsController extends Controller
{
    public function show(Channel $channel)
    {
        $channel->load('threads.channel');
        return view('channels.show', compact('channel'));
    }
}
