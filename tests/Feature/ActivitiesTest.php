<?php

namespace Tests\Feature;

use App\Constants\ActivityTypes;
use App\Models\Activity;
use App\Models\Channel;
use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ActivitiesTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $channel;
    protected $thread;

    /**
     * Set up the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create and authenticate a user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // Create a channel
        $this->channel = Channel::factory()->create();

        // Create a default thread
        $this->thread = Thread::factory()->create([
            'user_id' => $this->user->id,
            'channel_id' => $this->channel->id,
        ]);
    }

    /** @test */
    public function it_records_an_activity_when_a_thread_is_created()
    {
        $this->assertDatabaseCount('activities', 1);

        $this->assertDatabaseHas('activities', [
            'user_id' => $this->user->id,
            'activity_type' => 'thread_created',
            'target_id' => $this->thread->id,
            'target_type' => 'App\Models\Thread',
        ]);
    }

    /** @test */
    public function test_activity_relationships_are_correctly_associated()
    {
        $activity = Activity::first();

        $this->assertEquals($activity->target_id, $this->thread->id);
        $this->assertEquals($activity->user_id, $this->user->id);
    }

    /** @test */
    public function test_no_activity_recorded_when_thread_fails_to_save()
    {
        $threadData = Thread::factory()->raw([
            'title' => '',
            'channel_id' => $this->channel->id,
        ]);

        $response = $this->postJson(route('threads.store'), $threadData);
        $response->assertStatus(422);

        $this->assertDatabaseCount('threads', 1); // Default thread from setUp
        $this->assertDatabaseCount('activities', 1); // Activity from default thread
    }

    /** @test */
    public function test_multiple_threads_create_multiple_activity_records()
    {
        $anotherThread = Thread::factory()->create([
            'user_id' => $this->user->id,
            'channel_id' => $this->channel->id,
        ]);

        $this->assertDatabaseCount('activities', 2);

        $this->assertDatabaseHas('activities', [
            'user_id' => $this->user->id,
            'activity_type' => 'thread_created',
            'target_id' => $this->thread->id,
            'target_type' => 'App\Models\Thread',
        ]);

        $this->assertDatabaseHas('activities', [
            'user_id' => $this->user->id,
            'activity_type' => 'thread_created',
            'target_id' => $anotherThread->id,
            'target_type' => 'App\Models\Thread',
        ]);
    }

    /** @test */
    public function test_no_activity_recorded_for_unauthenticated_user_thread_creation()
    {
        \Illuminate\Support\Facades\Auth::logout();
        $threadData = Thread::factory()->raw([
            'channel_id' => $this->channel->id,
        ]);

        $response = $this->postJson(route('threads.store'), $threadData);
        $response->assertStatus(401);

        $this->assertDatabaseCount('activities', 1); // Activity from default thread
    }

    /** @test */
    public function test_activities_are_ordered_from_newest_to_oldest()
    {
        $anotherThread = Thread::factory()->create([
            'user_id' => $this->user->id,
            'channel_id' => $this->channel->id,
            'created_at' => now()->subSeconds(10),
        ]);

        $lastActivity = Activity::latest()->first();
        $secondActivity = Activity::latest()->skip(1)->first();

        $this->assertEquals($lastActivity->target_id, $this->thread->id);
        $this->assertEquals($secondActivity->target_id, $anotherThread->id);
    }

    /** @test */
    public function it_records_an_activity_when_a_reply_added()
    {
        $replyData = ['body' => 'This is a reply'];
        $response = $this->postJson(route('replies.store', ['channel' => $this->channel->slug, 'thread' => $this->thread->id]), $replyData);
        $response->assertStatus(201)
            ->assertJson([
                'body' => $replyData['body'],
                'user_id' => $this->user->id,
                'thread_id' => $this->thread->id,
            ]);

        $this->assertDatabaseHas('replies', [
            'body' => $replyData['body'],
            'user_id' => $this->user->id,
            'thread_id' => $this->thread->id,
        ]);

        $reply = Reply::first();

        $this->assertDatabaseCount('activities', 2);

        $this->assertDatabaseHas('activities', [
            'user_id' => $this->user->id,
            'activity_type' => ActivityTypes::REPLY_ADDED,
            'target_id' => $reply->id,
            'target_type' => 'App\Models\Reply',
        ]);
    }

    /** @test */
    public function test_reply_activity_relationships_are_correctly_associated()
    {
        $replyData = ['body' => 'This is a reply'];
        $response = $this->postJson(route('replies.store', ['channel' => $this->channel->slug, 'thread' => $this->thread->id]), $replyData);
        $response->assertStatus(201)
            ->assertJson([
                'body' => $replyData['body'],
                'user_id' => $this->user->id,
                'thread_id' => $this->thread->id,
            ]);

        $this->assertDatabaseHas('replies', [
            'body' => $replyData['body'],
            'user_id' => $this->user->id,
            'thread_id' => $this->thread->id,
        ]);

        $reply = Reply::first();
        $activity = Activity::where('activity_type', ActivityTypes::REPLY_ADDED)->first();

        $this->assertNotNull($activity, 'Reply activity was not recorded.');
        $this->assertEquals($activity->target_id, $reply->id);
        $this->assertEquals($activity->user_id, $this->user->id);
        $this->assertEquals(ActivityTypes::REPLY_ADDED, $activity->activity_type);
        $this->assertEquals('App\Models\Reply', $activity->target_type);
    }

    /** @test */
    public function test_no_activity_recorded_when_reply_fails_to_save()
    {
        $replyData = ['body' => ''];
        $response = $this->postJson(route('replies.store', ['channel' => $this->channel->slug, 'thread' => $this->thread->id]), $replyData);
        $response->assertStatus(422);

        $this->assertDatabaseCount('replies', 0);
        $this->assertDatabaseCount('activities', 1); // Activity from default thread
        $this->assertDatabaseMissing('activities', [
            'user_id' => $this->user->id,
            'activity_type' => ActivityTypes::REPLY_ADDED,
            'target_type' => 'App\Models\Reply',
        ]);
    }

    /** @test */
    public function test_multiple_replies_create_multiple_activity_records()
    {
        $replyData = ['body' => 'This is a reply'];
        $anotherReplyData = ['body' => 'This is another reply'];

        $response = $this->postJson(route('replies.store', ['channel' => $this->channel->slug, 'thread' => $this->thread->id]), $replyData);
        $anotherResponse = $this->postJson(route('replies.store', ['channel' => $this->channel->slug, 'thread' => $this->thread->id]), $anotherReplyData);

        $response->assertStatus(201)
            ->assertJson([
                'body' => $replyData['body'],
                'user_id' => $this->user->id,
                'thread_id' => $this->thread->id,
            ]);

        $anotherResponse->assertStatus(201)
            ->assertJson([
                'body' => $anotherReplyData['body'],
                'user_id' => $this->user->id,
                'thread_id' => $this->thread->id,
            ]);

        $this->assertDatabaseHas('replies', [
            'body' => $replyData['body'],
            'user_id' => $this->user->id,
            'thread_id' => $this->thread->id,
        ]);

        $this->assertDatabaseHas('replies', [
            'body' => $anotherReplyData['body'],
            'user_id' => $this->user->id,
            'thread_id' => $this->thread->id,
        ]);

        $reply1 = Reply::first();
        $reply2 = Reply::skip(1)->first();

        $this->assertDatabaseCount('activities', 3);

        $this->assertDatabaseHas('activities', [
            'user_id' => $this->user->id,
            'activity_type' => ActivityTypes::REPLY_ADDED,
            'target_id' => $reply1->id,
            'target_type' => 'App\Models\Reply',
        ]);

        $this->assertDatabaseHas('activities', [
            'user_id' => $this->user->id,
            'activity_type' => ActivityTypes::REPLY_ADDED,
            'target_id' => $reply2->id,
            'target_type' => 'App\Models\Reply',
        ]);
    }

    /** @test */
    public function test_no_activity_recorded_for_unauthenticated_user_reply_creation()
    {
        \Illuminate\Support\Facades\Auth::logout();

        $replyData = ['body' => 'This is a reply'];

        $response = $this->postJson(route('replies.store', ['channel' => $this->channel->slug, 'thread' => $this->thread->id]), $replyData);
        $response->assertStatus(401);

        $this->assertDatabaseCount('replies', 0);
        $this->assertDatabaseCount('activities', 1); // Activity from default thread
    }

    /** @test */
    public function test_activity_records_deletion_on_thread_deletion()
    {
        $reply = Reply::factory()->create([
            'thread_id' => $this->thread->id
        ]);

        $response = $this->post(route('reply.favorite.store', $reply));
        $response->assertStatus(201)->assertJson(['message' => 'Reply favorited']);

        $this->assertDatabaseHas('activities', [
            'user_id' => $this->user->id,
            'activity_type' => ActivityTypes::THREAD_CREATED,
            'target_id' => $this->thread->id,
            'target_type' => 'App\Models\Thread',
        ]);

        $this->assertDatabaseHas('threads', [
            'user_id' => $this->thread->user_id,
            'title' => $this->thread->title,
        ]);

        $this->delete(route('threads.destroy', $this->thread->id))
            ->assertStatus(200)
            ->assertJson(['message' => 'Thread deleted successfully.']);

        $this->assertDatabaseMissing('activities', [
            'user_id' => $this->user->id,
            'activity_type' => ActivityTypes::REPLY_ADDED,
            'target_id' => $reply->id,
            'target_type' => 'App\Models\Reply',
        ]);

        $this->assertDatabaseMissing('threads', [
            'user_id' => $this->thread->user_id,
            'title' => $this->thread->title,
        ]);

        $this->assertDatabaseMissing('activities', [
            'user_id' => $this->user->id,
            'activity_type' => ActivityTypes::THREAD_CREATED,
            'target_id' => $this->thread->id,
            'target_type' => 'App\Models\Thread',
        ]);

        $this->assertDatabaseMissing('activities', [
            'user_id' => $this->user->id,
            'activity_type' => ActivityTypes::REPLY_FAVORITED,
            'target_id' => $reply->id,
            'target_type' => 'App\Models\Reply',
        ]);

        $this->assertDatabaseCount('activities', 0);
    }

    /** @test */
    public function it_records_an_activity_when_a_reply_favorited()
    {
        $reply = Reply::factory()->create([
            'thread_id' => $this->thread->id
        ]);
        $response = $this->post(route('reply.favorite.store', $reply));
        $response->assertStatus(201)->assertJson(['message' => 'Reply favorited']);
        $this->assertDatabaseHas('favorite_replies', [
            'user_id' => $this->user->id,
            'reply_id' => $reply->id
        ]);
        $this->assertDatabaseCount('activities', 3);
        $this->assertDatabaseHas('activities', [
            'user_id' => $this->user->id,
            'activity_type' => ActivityTypes::REPLY_FAVORITED,
            'target_id' => $reply->id,
            'target_type' => 'App\Models\Reply',
        ]);
    }

    /** @test */
    public function test_activity_record_deletion_when_remove_the_reply_from_favorites()
    {
        $reply = Reply::factory()->create([
            'thread_id' => $this->thread->id
        ]);

        $response = $this->post(route('reply.favorite.store', $reply));

        $response->assertStatus(201)->assertJson(['message' => 'Reply favorited']);
        $this->assertDatabaseHas('favorite_replies', [
            'user_id' => $this->user->id,
            'reply_id' => $reply->id
        ]);
        $this->assertDatabaseCount('activities', 3);
        $this->assertDatabaseHas('activities', [
            'user_id' => $this->user->id,
            'activity_type' => ActivityTypes::REPLY_FAVORITED,
            'target_id' => $reply->id,
            'target_type' => 'App\Models\Reply',
        ]);

        $anotherResponse = $this->delete(route('reply.favorite.delete', $reply));
        $anotherResponse->assertStatus(200)->assertJson(['message' => 'Reply Favorite deleted successfully']);

        $this->assertDatabaseMissing('favorite_replies', [
            'user_id' => $this->user->id,
            'reply_id' => $reply->id
        ]);
        $this->assertDatabaseCount('activities', 2);
        $this->assertDatabaseMissing('activities', [
            'user_id' => $this->user->id,
            'activity_type' => ActivityTypes::REPLY_FAVORITED,
            'target_id' => $reply->id,
            'target_type' => 'App\Models\Reply',
        ]);
    }

    /** @test */
    public function an_authenticated_user_tries_to_delete_a_reply_that_they_did_not_favorite()
    {
        $reply = Reply::factory()->create([
            'thread_id' => $this->thread->id
        ]);

        $response = $this->delete(route('reply.favorite.delete', $reply));
        $response->assertStatus(422)->assertJson(['message' => 'Reply was not favorited']);

        $this->assertDatabaseMissing('favorite_replies', [
            'user_id' => $this->user->id,
            'reply_id' => $reply->id
        ])->assertDatabaseCount('favorite_replies', 0);

        $this->assertDatabaseMissing('activities', [
            'user_id' => $this->user->id,
            'activity_type' => ActivityTypes::REPLY_FAVORITED,
            'target_id' => $reply->id,
            'target_type' => 'App\Models\Reply',
        ])->assertDatabaseCount('activities', 2);
    }

    /** @test */
    public function an_authenticated_user_tries_to_delete_another_user_s_reply_from_their_favorites()
    {
        $reply = Reply::factory()->create([
            'user_id' => $this->user->id,
            'thread_id' => $this->thread->id
        ]);

        $response = $this->post(route('reply.favorite.store', $reply));

        $response->assertStatus(201)->assertJson(['message' => 'Reply favorited']);
        $this->assertDatabaseHas('favorite_replies', [
            'user_id' => $this->user->id,
            'reply_id' => $reply->id
        ]);
        $this->assertDatabaseCount('activities', 3);
        $this->assertDatabaseHas('activities', [
            'user_id' => $this->user->id,
            'activity_type' => ActivityTypes::REPLY_FAVORITED,
            'target_id' => $reply->id,
            'target_type' => 'App\Models\Reply',
        ]);

        \Illuminate\Support\Facades\Auth::logout();

        $anotherUser = User::factory()->create();
        $this->actingAs($anotherUser);

        $response = $this->delete(route('reply.favorite.delete', $reply));
        $response->assertStatus(422)->assertJson(['message' => 'Reply was not favorited']);

        $this->assertDatabaseHas('favorite_replies', [
            'user_id' => $this->user->id,
            'reply_id' => $reply->id
        ])->assertDatabaseCount('favorite_replies', 1);

        $this->assertDatabaseHas('activities', [
            'user_id' => $this->user->id,
            'activity_type' => ActivityTypes::REPLY_FAVORITED,
            'target_id' => $reply->id,
            'target_type' => 'App\Models\Reply',
        ])->assertDatabaseCount('activities', 3);
    }
}
