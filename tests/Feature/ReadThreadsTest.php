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
        $this->assertEquals($this->thread->id, $this->reply->thread_id);
        $this->assertTrue($this->thread->replies->contains($this->reply));
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
            'channel' => $this->channel->slug,
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
            'channel' => $this->channel->slug,  // Assuming the thread belongs to a channel
            'thread' => $this->thread->id  // Or whatever identifier you use for threads
        ]), false);

        $response = $this->get(route('threads.show', [
            'channel' => $this->channel->slug,
            'thread' => $this->thread->id
        ]));
        $response->assertStatus(200);
        $response->assertSee($this->thread->title, false);
    }

    /** @test */
    public function guest_can_view_single_thread()
    {
        $response = $this->get(route('threads.show', ['channel' => $this->channel->slug, 'thread' => $this->thread->id]));

        $response->assertStatus(200)
            ->assertViewIs('threads.show')
            ->assertSee($this->thread->title)
            ->assertSee($this->thread->body)
            ->assertSee($this->user->name);
    }

    /** @test */
    public function thread_page_displays_associated_replies()
    {
        $response = $this->get(route('threads.show', ['channel' => $this->channel->slug, 'thread' => $this->thread->id]));
        $response->assertStatus(200)
            ->assertSee($this->thread->title)
            ->assertSee($this->thread->body)
            ->assertSee($this->reply->body)
            ->assertSee($this->user->name);
    }

    /** @test */
    public function thread_page_displays_associated_replies_and_count()
    {
        Reply::factory()->count(2)->create([
            'thread_id' => $this->thread->id,
        ]);

        $response = $this->get(route('threads.show', ['channel' => $this->channel->slug, 'thread' => $this->thread->id]));

        $response->assertStatus(200)
            ->assertSee($this->thread->title)
            ->assertSee($this->thread->body)
            ->assertSee($this->reply->body)
            ->assertSee($this->user->name)
            ->assertSee('Replies: 3')
            ->assertSee('3')
            ->assertSee($this->thread->created_at->diffForHumans());
    }

    /** @test */
    public function thread_page_shows_correct_reply_text_according_count()
    {
        $channel = Channel::factory()->create();
        $thread = Thread::factory()->create([
            'channel_id' => $channel->id
        ]);
        // Test with 0 replies
        $response = $this->get(route('threads.show', [
            'channel' => $channel->slug,
            'thread' => $thread->id
        ]));
        $response->assertSee('Replies: 0');

        // Test with 1 reply
        Reply::factory()->create([
            'thread_id' => $thread->id,
        ]);

        $response = $this->get(route('threads.show', [
            'channel' => $channel->slug,
            'thread' => $thread->id
        ]));
        $response->assertSee('Reply: 1');

        // Test with multiple replies
        Reply::factory()->count(2)->create([
            'thread_id' => $this->thread->id,
        ]);

        $response = $this->get(route('threads.show', [
            'channel' => $this->channel->slug,
            'thread' => $this->thread->id
        ]));
        $response->assertSee('Replies: 3');
    }

    /** @test */
    public function accessing_nonexistent_thread_returns_404()
    {
        $response = $this->get(route('threads.show', ['channel' => $this->channel->slug, 'thread' => 999999999]));

        $response->assertStatus(404);
    }

    /** @test */
    public function a_user_can_filter_threads_according_channel()
    {
        $channel1 = $this->channel;

        $threadByChannel1 = Thread::factory()->create([
            'channel_id' => $channel1->id
        ]);

        $threadByChannel2 = Thread::factory()->create();

        $this->get(route('threads.index', ['channel' => $channel1->name]))
            ->assertStatus(200)
            ->assertSeeText([$threadByChannel1->title, $threadByChannel1->body])
            ->assertDontSeeText([$threadByChannel2->title, $threadByChannel2->body]);
    }

    /** @test */
    public function it_shows_all_threads_when_channel_not_found()
    {
        $channel = Channel::factory()->create(['name' => 'Programming']);
        $thread = Thread::factory()->create(['channel_id' => $channel->id]);

        $response = $this->get(route('threads.index', ['channel' => 'NonexistentChannel123']));

        $response->assertStatus(200);
        $response->assertSee($thread->title);
        $response->assertSee("Channel 'NonexistentChannel123' not found. Showing all threads.");
    }

    /** @test */
    public function a_user_can_filter_threads_according_username()
    {
        $user = User::factory()->create([
            'name' => 'حسین'
        ]);
        $this->actingAs($user);

        $threadByUser1 = Thread::factory()->create([
            'user_id' => $user->id,
        ]);
        $threadByUser2 = Thread::factory()->create();

        $this->get(route('threads.index', ['by' => $user->name]))
            ->assertStatus(200)
            ->assertSeeText([$threadByUser1->title, $threadByUser1->body])
            ->assertDontSeeText([$threadByUser2->title, $threadByUser2->body]);
    }

    /** @test */
    public function it_shows_all_threads_when_username_not_found()
    {
        $user = User::factory()->create(['name' => 'حسین']);
        $thread = Thread::factory()->create(['user_id' => $user->id]);

        $response = $this->get(route('threads.index', ['by' => 'NonexistentUser123']));

        $response->assertStatus(200);
        $response->assertSee($thread->title);
        $response->assertSee("User 'NonexistentUser123' not found. Showing all threads.");
    }

    /** @test */
    public function it_shows_all_threads_when_no_username_provided()
    {
        $thread1 = Thread::factory()->create();
        $thread2 = Thread::factory()->create();

        $response = $this->get(route('threads.index'));

        $response->assertStatus(200);
        $response->assertSee($thread1->title);
        $response->assertSee($thread2->title);
    }

    protected function createThreadWithReplies($replyCount, $createdAt = null)
    {
        $thread = Thread::factory()->create(['created_at' => $createdAt ?: now()]);
        Reply::factory()->count($replyCount)->create([
            'thread_id' => $thread->id,
            'created_at' => $createdAt ?: now()
        ]);
        return $thread;
    }

    /** @test  */
    public function a_user_can_filter_threads_according_popularity()
    {
        $thread1 = $this->createThreadWithReplies(5, now()->subSeconds(2));
        $thread2 = $this->createThreadWithReplies(2, now()->subSecond(1));
        $thread3 = $this->createThreadWithReplies(0, now());

        $this->get(route('threads.index', ['popular' => true]))
            ->assertStatus(200)
            ->assertSeeInOrder([$thread3->title, $thread2->title, $thread1->title]);
    }

    /** @test */
    public function show_threads_by_ascending_replies_when_popularity_is_false()
    {
        $thread1 = $this->createThreadWithReplies(0, now()->subSeconds(2));
        $thread2 = $this->createThreadWithReplies(2, now()->subSecond(1));
        $thread3 = $this->createThreadWithReplies(5, now());

        $this->get(route('threads.index', ['popular' => false]))
            ->assertStatus(200)
            ->assertSeeInOrder([$thread3->title, $thread2->title, $thread1->title]);
    }

    /** @test */
    public function show_threads_according_latest_if_popularity_not_exist()
    {
        $thread1 = $this->createThreadWithReplies(0, now());
        $thread2 = $this->createThreadWithReplies(2, now()->addSeconds(10));
        $thread3 = $this->createThreadWithReplies(5, now()->addSeconds(20));

        $this->get(route('threads.index'))
            ->assertStatus(200)
            ->assertSeeInOrder([$thread3->title, $thread2->title, $thread1->title]);
    }

    /** @test */
    public function show_threads_according_latest_if_popularity_not_valid()
    {
        $thread1 = $this->createThreadWithReplies(0, now());
        $thread2 = $this->createThreadWithReplies(2, now()->addSeconds(10));
        $thread3 = $this->createThreadWithReplies(5, now()->addSeconds(20));

        $this->get(route('threads.index', ['popular' => 'invalid123']))
            ->assertStatus(200)
            ->assertSeeInOrder([$thread3->title, $thread2->title, $thread1->title]);
    }

    /** @test */
    public function show_thread_sets_is_favorited_correctly_for_authenticated_user()
    {
        $user = $this->user;
        $this->actingAs($user);

        $thread = $this->thread;

        $reply1 = Reply::factory()->create([
            'thread_id' => $thread->id
        ]);

        $reply2 = Reply::factory()->create([
            'thread_id' => $thread->id
        ]);

        $reply1->favoritedBy()->attach($user->id);

        $response = $this->get(route('threads.show', ['channel' => $this->channel->slug, 'thread' => $thread->id]));

        $response->assertStatus(200);
        $threadData = $response->viewData('thread');
        $replies = $threadData->replies;

        $this->assertTrue($replies->firstWhere('id', $reply1->id)->is_favorited);
        $this->assertFalse($replies->firstWhere('id', $reply2->id)->is_favorited);
    }

    /** @test */
    public function show_thread_sets_is_favorited_to_false_for_unauthenticated_user()
    {
        $user = $this->user;

        $thread = $this->thread;

        $reply1 = Reply::factory()->create([
            'thread_id' => $thread->id
        ]);

        $reply2 = Reply::factory()->create([
            'thread_id' => $thread->id
        ]);

        $reply1->favoritedBy()->attach($user->id);

        $response = $this->get(route('threads.show', ['channel' => $this->channel->slug, 'thread' => $thread->id]));

        $response->assertStatus(200);
        $threadData = $response->viewData('thread');
        $replies = $threadData->replies;

        $this->assertFalse($replies->firstWhere('id', $reply1->id)->is_favorited);
        $this->assertFalse($replies->firstWhere('id', $reply2->id)->is_favorited);
    }
}
