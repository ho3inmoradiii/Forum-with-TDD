<?php

namespace Tests\Feature;

use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SubscribeThreadsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_authenticated_user_can_subscribe_a_test()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $thread = Thread::factory()->create();

        $response = $this->post(route('subscribe.thread.store', $thread));
        $response->assertStatus(201)->assertJson(['message' => 'Thread Subscribed']);
        $this->assertDatabaseHas('subscribe_threads', [
            'user_id' => $user->id,
            'thread_id' => $thread->id
        ]);
    }

    /** @test */
    public function an_authenticated_user_can_remove_the_thread_subscription()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $thread = Thread::factory()->create();
        $thread->subscribers()->attach($user->id);

        $response = $this->delete(route('subscribe.thread.delete', $thread));
        $response->assertStatus(200)->assertJson(['message' => 'Thread Subscription deleted successfully']);

        $this->assertDatabaseMissing('subscribe_threads', [
            'user_id' => $user->id,
            'thread_id' => $thread->id
        ]);
    }
}
