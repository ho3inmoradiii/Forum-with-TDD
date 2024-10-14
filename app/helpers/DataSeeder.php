<?php

namespace App\helpers;

use App\Models\Thread;
use App\Models\Reply;

class DataSeeder
{
    public static function seedReplies()
    {
        Thread::latest()->take(50)->get()->each(function ($thread) {
            Reply::factory()->count(10)->create(['thread_id' => $thread->id]);
        });
    }
}
