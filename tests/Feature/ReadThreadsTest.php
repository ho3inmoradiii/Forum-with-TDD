<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadThreadsTest extends TestCase
{
    use RefreshDatabase;

    protected $thread;
    protected $reply;
    protected $user;
    protected $channel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->channel = Channel::factory()->create();
        $this->user = User::factory()->create();
        $this->thread = Thread::factory()->create([
            'user_id' => $this->user->id,
            'channel_id' => $this->channel->id
        ]);
        $this->reply = Reply::factory()->create([
            'user_id' => $this->user->id,
            'thread_id' => $this->thread->id,
        ]);
    }

    /** @test */
    public function a_thread_has_replies()
    {
        $this->assertInstanceOf(Collection::class, $this->thread->replies);
        $this->assertInstanceOf(Reply::class, $this->thread->replies->first());
    }

    /** @test */
    public function a_thread_belongs_to_a_user()
    {
        $this->assertInstanceOf(User::class, $this->thread->user);
    }

    /** @test */
    public function a_reply_belongs_to_thread()
    {
        $this->assertInstanceOf(Thread::class, $this->reply->thread);
    }

    /** @test */
    public function a_reply_belongs_to_user()
    {
        $this->assertInstanceOf(User::class, $this->reply->user);
    }

    /** @test */
    public function thread_show_route_pass_correct_data_to_view()
    {
        $response = $this->get(route('threads.show', [
            'channelId' => $this->channel->id,
            'thread' => $this->thread->id
        ]));

        $response->assertViewHas('thread', function ($viewThread) {
            return $viewThread instanceof Thread
                && $viewThread->id === $this->thread->id
                && $viewThread->replies->contains($this->reply);
        });
    }

    /** @test */
    public function guest_can_view_threads_list()
    {
        $response = $this->get('/threads');
        $response->assertStatus(200)
            ->assertViewIs('threads.index')
            ->assertSee($this->thread->title);
    }

    /** @test */
    public function thread_titles_are_links_to_thread_pages()
    {
        $response = $this->get('/threads');

        $response->assertStatus(200);

        $response->assertSee($this->thread->title, false);
        $response->assertSee(route('threads.show', [
            'channelId' => $this->channel->id,  // Assuming the thread belongs to a channel
            'thread' => $this->thread->id  // Or whatever identifier you use for threads
        ]), false);

        $response = $this->get(route('threads.show', [
            'channelId' => $this->channel->id,
            'thread' => $this->thread->id
        ]));
        $response->assertStatus(200);
        $response->assertSee($this->thread->title, false);
    }

    /** @test */
    public function guest_can_view_single_thread()
    {
        $response = $this->get(route('threads.show', ['channelId' => $this->channel->id, 'thread' => $this->thread->id]));

        $response->assertStatus(200)
            ->assertViewIs('threads.show')
            ->assertSee($this->thread->title)
            ->assertSee($this->thread->body)
            ->assertSee($this->user->name);
    }

    /** @test */
    public function thread_page_displays_associated_replies()
    {
        $response = $this->get(route('threads.show', ['channelId' => $this->channel->id, 'thread' => $this->thread->id]));
        $response->assertStatus(200)
            ->assertSee($this->thread->title)
            ->assertSee($this->thread->body)
            ->assertSee($this->reply->body)
            ->assertSee($this->user->name);
    }

    /** @test */
    public function accessing_nonexistent_thread_returns_404()
    {
        $response = $this->get('/threads/999999');

        $response->assertStatus(404);
    }
}
