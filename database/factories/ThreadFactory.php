<?php

namespace Database\Factories;

use App\Models\Channel;
use App\Models\Thread;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class ThreadFactory extends Factory
{
    protected $model = Thread::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'body' => $this->faker->paragraph,
            'user_id' => User::factory(),
            'channel_id' => Channel::factory(),
        ];
    }
}
