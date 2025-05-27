<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SubscribeThreadsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $thread;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->thread = Thread::factory()->create();
    }

    /** @test */
    public function an_authenticated_user_can_subscribe_a_thread()
    {
        $user = $this->user;
        $this->actingAs($user);

        $thread = $this->thread;

        $response = $this->post(route('subscribe.thread.store', $thread));
        $response->assertStatus(201)->assertJson(['message' => 'Thread Subscribed']);
        $this->assertDatabaseHas('subscribe_threads', [
            'user_id' => $user->id,
            'thread_id' => $thread->id
        ]);
    }

    /** @test */
    public function it_registers_notification_for_subscriptions_when_reply_is_added_to_thread()
    {
        $user = $this->user;
        $channel = Channel::factory()->create();
        $thread = Thread::factory()->create([
            'channel_id' => $channel->id
        ]);
        $thread->subscribers()->attach($user->id);

        $authenticatedUser  = User::factory()->create();
        $this->actingAs($authenticatedUser);

        $replyData = ['body' => 'This is a reply'];
        $response = $this->postJson(route('replies.store', ['channel' => $channel->slug, 'thread' => $thread->id]), $replyData);

        $response->assertStatus(201)
            ->assertJson([
                'body' => $replyData['body'],
                'user_id' => $authenticatedUser->id,
                'thread_id' => $thread->id,
            ]);

        $this->assertDatabaseCount('notifications', 1)
            ->assertDatabaseHas('notifications', [
                'type' => 'App\Notifications\NewReplyNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $user->id
            ]);
    }

    /** @test */
    public function an_authenticated_user_can_remove_the_thread_subscription()
    {
        $user = $this->user;
        $this->actingAs($user);

        $thread = $this->thread;
        $thread->subscribers()->attach($user->id);

        $response = $this->delete(route('subscribe.thread.delete', $thread));
        $response->assertStatus(200)->assertJson(['message' => 'Thread Subscription deleted successfully']);

        $this->assertDatabaseMissing('subscribe_threads', [
            'user_id' => $user->id,
            'thread_id' => $thread->id
        ]);
    }

    /** @test */
    public function an_authenticated_user_tries_to_re_subscribe_a_thread_that_they_previously_subscribed()
    {
        $user = $this->user;
        $this->actingAs($user);

        $thread = $this->thread;
        $thread->subscribers()->attach($user->id);

        $response = $this->post(route('subscribe.thread.store', $thread));

        $response->assertStatus(422)->assertJson(['message' => 'Thread already subscribed']);

        $this->assertDatabaseHas('subscribe_threads', [
            'user_id' => $user->id,
            'thread_id' => $thread->id
        ])->assertDatabaseCount('subscribe_threads', 1);
    }

    /** @test */
    public function an_authenticated_user_tries_to_delete_a_thread_that_they_did_not_subscribe()
    {
        $user = $this->user;
        $this->actingAs($user);

        $thread = $this->thread;

        $response = $this->delete(route('subscribe.thread.delete', $thread));
        $response->assertStatus(422)->assertJson(['message' => 'Thread was not subscribed']);

        $this->assertDatabaseMissing('subscribe_threads', [
            'user_id' => $user->id,
            'thread_id' => $thread->id
        ])->assertDatabaseCount('subscribe_threads', 0);
    }

    /** @test */
    public function a_user_who_is_not_logged_in_cannot_subscribe_a_thread()
    {
        $user = $this->user;
        $thread = $this->thread;

        $response = $this->post(route('subscribe.thread.store', $thread));
        $response->assertStatus(302)->assertRedirect('/login');

        $this->assertDatabaseMissing('subscribe_threads', [
            'user_id' => $user->id,
            'thread_id' => $thread->id
        ])->assertDatabaseCount('subscribe_threads', 0);
    }

    /** @test */
    public function a_user_cannot_remove_a_thread_from_subscriptions_without_logging_in()
    {
        $user = $this->user;
        $thread = $this->thread;

        $thread->subscribers()->attach($user->id);

        $response = $this->delete(route('subscribe.thread.delete', $thread));
        $response->assertStatus(302)->assertRedirect('/login');

        $this->assertDatabaseHas('subscribe_threads', [
            'user_id' => $user->id,
            'thread_id' => $thread->id
        ])->assertDatabaseCount('subscribe_threads', 1);
    }

    /** @test */
    public function a_user_cannot_remove_another_user_s_thread_from_their_subscriptions()
    {
        $user1 = $this->user;
        $user2 = User::factory()->create();

        $this->actingAs($user1);

        $thread = $this->thread;

        $thread->subscribers()->attach($user2->id);

        $response = $this->delete(route('subscribe.thread.delete', $thread));
        $response->assertStatus(422)->assertJson(['message' => 'Thread was not subscribed']);

        $this->assertDatabaseHas('subscribe_threads', [
            'user_id' => $user2->id,
            'thread_id' => $thread->id
        ])->assertDatabaseCount('subscribe_threads', 1);
    }

    /** @test */
    public function a_user_wants_to_favorite_or_delete_a_reply_that_does_not_exist()
    {
        $user = $this->user;

        $this->actingAs($user);

        $response1 = $this->delete(route('subscribe.thread.delete', 999));
        $response1->assertStatus(404);

        $response2 = $this->post(route('reply.favorite.store', 999));
        $response2->assertStatus(404);
    }
}
