<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Thread;
use App\Models\User;
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

    /** @test */
    public function authenticated_user_can_delete_own_thread()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $thread = Thread::factory()->create([
            'user_id' => $user->id
        ]);

        $this->delete(route('threads.destroy', ['thread' => $thread]))
            ->assertStatus(200)
            ->assertJson(['message' => 'Thread deleted successfully.']);

        $this->assertDatabaseMissing('threads', [
            'user_id' => $thread->user_id,
            'title' => $thread->title
        ]);
    }

    /** @test */
    public function a_user_cannot_delete_other_users_threads()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $otherUser = User::factory()->create();

        $thread = Thread::factory()->create([
            'user_id' => $otherUser->id
        ]);

        $this->delete(route('threads.destroy', ['thread' => $thread]))
            ->assertStatus(403)
            ->assertJson(['message' => 'You do not have permission to delete this thread.']);

        $this->assertDatabaseHas('threads', [
            'user_id' => $thread->user_id,
            'title' => $thread->title
        ]);
    }
}
