<?php

namespace App\Http\View\Composers;

use App\Models\Channel;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ChannelComposer
{
    public function compose(View $view)
    {
        $channels = Cache::remember('channels', now()->addHour(), function () {
            return Channel::all();
        });

        $view->with('channels', $channels);
    }
}
