<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ParticipateInForumTest extends TestCase
{
    use RefreshDatabase;

    protected $thread;
    protected $channel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->channel = Channel::factory()->create();
        $this->thread = Thread::factory()->create([
            'channel_id' => $this->channel->id,
        ]);
    }

    /** @test */
    public function a_thread_can_add_reply()
    {
        $reply = Reply::factory()->make();

        $this->thread->addReply($reply);

        $this->assertDatabaseHas('replies', [
            'thread_id' => $this->thread->id,
            'body' => $reply->body
        ]);
    }

    /** @test */
    public function unauthenticated_user_may_not_add_reply()
    {
        $reply = ['body' => 'This is a reply'];
        $response = $this->postJson(route('replies.store', ['channel' => $this->channel->slug, 'thread' => $this->thread]), $reply);

        $response->assertStatus(401);
    }

    /** @test */
    public function an_authenticated_user_can_participate_in_forum_threads()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // When the user adds a reply to the thread
        $reply = ['body' => 'This is a reply'];
        $response = $this->postJson(route('replies.store', ['channel' => $this->channel->slug, 'thread' => $this->thread]), $reply);

        // Then the response should be successful and contain the reply data
        $response->assertStatus(201)
            ->assertJson([
                'body' => $reply['body'],
                'user_id' => $user->id,
                'thread_id' => $this->thread->id
            ]);

        $this->assertDatabaseHas('replies', [
            'body' => $reply['body'],
            'user_id' => $user->id,
            'thread_id' => $this->thread->id
        ]);

        // Now, let's check if the user can see their reply on the thread page
        $threadResponse = $this->get(route('threads.show', ['channel' => $this->channel->slug, 'thread' => $this->thread]));

        $threadResponse->assertStatus(200)
            ->assertSee($reply['body'])
            ->assertSee($user->name);
    }

    /** @test */
    public function an_authenticated_user_can_publish_a_thread()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $channel = Channel::factory()->create();

        $thread = Thread::factory()->raw([
            'channel_id' => $channel->id
        ]);

        $response = $this->postJson(route('threads.store'), $thread);
        $response->assertStatus(201)
            ->assertJson([
                'thread' => [
                    'body' => $thread['body'],
                    'title' => $thread['title'],
                    'user_id' => $user->id,
                    'channel_id' => $channel->id,
                ],
                'message' => 'Thread created successfully'
            ]);

        $this->assertDatabaseHas('threads', [
            'body' => $thread['body'],
            'title' => $thread['title'],
            'user_id' => $user->id,
            'channel_id' => $channel->id,
        ]);

        $threadResponse = $this->get(route('threads.index'));
        $threadResponse->assertSeeText([$thread['title'], $thread['body']]);
    }

    /**
     * @dataProvider threadValidationProvider
     */
    public function test_thread_requires_valid_fields($field, $value)
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $thread = Thread::factory()->raw([
            $field => $value
        ]);

        $response = $this->postJson(route('threads.store'), $thread);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([$field]);
    }

    public function threadValidationProvider()
    {
        return [
            'title is required' => ['title', null],
            'body is required' => ['body', null],
            'channel_id is required' => ['channel_id', null],
        ];
    }

//    /** @test */
//    public function a_thread_requires_a_valid_channel_id()
//    {
//        $user = User::factory()->create();
//        $this->actingAs($user);
//
//        $channel = Channel::factory()->create();
//
//        $thread = Thread::factory()->make(['channel_id' => null]);
//        $this->post(route('threads.store'), $thread->toArray())
//            ->assertSessionHasErrors('channel_id');
//
//        $thread = Thread::factory()->make(['channel_id' => 999]); // Non-existent channel id
//        $this->post(route('threads.store'), $thread->toArray())
//            ->assertSessionHasErrors('channel_id');
//
//        $thread = Thread::factory()->make(['channel_id' => $channel->id]);
//        $this->post(route('threads.store'), $thread->toArray())
//            ->assertSessionDoesntHaveErrors('channel_id');
//    }

    /** @test */
    public function unauthenticated_user_can_not_publish_a_thread()
    {
        $thread = Thread::factory()->raw();

        $response = $this->postJson(route('threads.store'), $thread);

        $response->assertStatus(401);
    }

    /** @test  */
    public function unauthenticated_user_can_not_see_thread_create_page()
    {
        $response = $this->get(route('threads.create'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_user_can_update_own_reply()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $reply = Reply::factory()->create([
            'user_id' => $user->id,
            'thread_id' => $this->thread->id,
        ]);

        $replyRaw = Reply::factory()->raw([
            'body' => 'test body for update'
        ]);

        $response = $this->putJson(route('replies.update', $reply->id), $replyRaw);
        $response->assertStatus(200)
            ->assertJson([
                'body' => $replyRaw['body'],
                'user_id' => $user->id,
                'id' => $reply->id,
                'thread_id' => $this->thread->id,
            ]);

        $this->assertDatabaseHas('replies', [
            'body' => $replyRaw['body'],
            'user_id' => $user->id,
            'id' => $reply->id,
            'thread_id' => $this->thread->id,
        ])->assertDatabaseCount('replies', 1);
    }

    /** @test */
    public function authenticated_user_attempts_to_update_another_user_s_reply()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $anotherUser = User::factory()->create();

        $reply = Reply::factory()->create([
            'user_id' => $anotherUser->id,
            'thread_id' => $this->thread->id,
        ]);

        $replyRaw = Reply::factory()->raw([
            'body' => 'test body for update'
        ]);

        $response = $this->putJson(route('replies.update', $reply->id), $replyRaw);
        $response->assertStatus(403)
            ->assertJson(['message' => 'You do not have permission to update this reply.']);;

        $this->assertDatabaseHas('replies', [
            'body' => $reply->body,
            'user_id' => $anotherUser->id,
            'id' => $reply->id,
            'thread_id' => $this->thread->id,
        ])->assertDatabaseCount('replies', 1);
    }

    /** @test */
    public function unauthenticated_user_cannot_update_any_replies()
    {
        $reply = Reply::factory()->create([
            'thread_id' => $this->thread->id,
        ]);

        $replyRaw = Reply::factory()->raw([
            'body' => 'test body for update'
        ]);

        $this->putJson(route('replies.update', $reply->id), $replyRaw)
            ->assertStatus(401);

        $this->assertDatabaseHas('replies', [
            'user_id' => $reply->user_id,
            'body' => $reply->body,
            'thread_id' => $reply->thread_id
        ]);
    }

    /** @test */
    public function test_update_reply_requires_valid_fields()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $reply = Reply::factory()->create([
            'user_id' => $user->id,
            'thread_id' => $this->thread->id,
        ]);

        $replyData = ['body' => ''];

        $response = $this->putJson(route('replies.update', $reply->id), $replyData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['body']);
    }
}
