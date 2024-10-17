<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Thread;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ThreadChannelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_thread_belongs_to_a_channel()
    {
        $channel = Channel::factory()->create();
        $thread = Thread::factory()->create([
            'channel_id' => $channel->id
        ]);

        $this->assertInstanceOf(Channel::class, $thread->channel);
        $this->assertEquals($channel->id, $thread->channel->id);
    }

    /** @test */
    public function a_channel_has_many_threads()
    {
        $channel = Channel::factory()->create();
        $thread = Thread::factory()->create([
            'channel_id' => $channel->id
        ]);

        $this->assertInstanceOf(Thread::class, $channel->threads->first());
        $this->assertTrue($channel->threads->contains($thread));
    }
}
