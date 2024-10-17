<?php

namespace Tests\Feature;

use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ParticipateInForumTest extends TestCase
{
    use RefreshDatabase;

    protected $thread;

    protected function setUp(): void
    {
        parent::setUp();
        $this->thread = Thread::factory()->create();
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
        $response = $this->postJson(route('replies.store', $this->thread), $reply);

        $response->assertStatus(401);
    }

    /** @test */
    public function an_authenticated_user_can_participate_in_forum_threads()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // When the user adds a reply to the thread
        $reply = ['body' => 'This is a reply'];
        $response = $this->postJson(route('replies.store', $this->thread), $reply);

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
        $threadResponse = $this->get(route('threads.show', $this->thread));

        $threadResponse->assertStatus(200)
            ->assertSee($reply['body'])
            ->assertSee($user->name);
    }

    /** @test */
    public function an_authenticated_user_can_publish_a_thread()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $thread = [
            'title' => 'Thread title',
            'body' => 'Thread body'
        ];

        $response = $this->postJson(route('threads.store'), $thread);
        $response->assertStatus(201)
            ->assertJson([
                'body' => $thread['body'],
                'title' => $thread['title'],
                'user_id' => $user->id
            ]);

        $this->assertDatabaseHas('threads', [
            'body' => $thread['body'],
            'title' => $thread['title'],
            'user_id' => $user->id
        ]);

        $threadResponse = $this->get(route('threads.index'));
        $threadResponse->assertSeeText([$thread['title'], $thread['body']]);
    }

    /** @test */
    public function unauthenticated_user_can_not_publish_a_thread()
    {
        $thread = Thread::factory()->raw();

        $response = $this->postJson(route('threads.store'), $thread);

        $response->assertStatus(401);
    }
}
