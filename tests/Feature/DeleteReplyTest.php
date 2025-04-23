<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DeleteReplyTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $channel;
    protected $thread;

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
    public function authenticated_user_can_delete_own_reply()
    {
        $reply = Reply::factory()->create([
            'user_id' => $this->user->id,
            'thread_id' => $this->thread->id,
        ]);

        $this->delete(route('replies.destroy', $reply->id))
            ->assertStatus(200)
            ->assertJson(['message' => 'Reply deleted successfully.']);

        $this->assertDatabaseMissing('replies', [
            'user_id' => $reply->user_id,
            'body' => $reply->body,
            'thread_id' => $reply->thread_id,
        ]);
    }

    /** @test */
    public function a_user_cannot_delete_other_users_replies()
    {
        $otherUser = User::factory()->create();

        $reply = Reply::factory()->create([
            'user_id' => $otherUser->id,
            'thread_id' => $this->thread->id,
        ]);

        $this->delete(route('replies.destroy', $reply->id), [], ['Accept' => 'application/json'])
            ->assertStatus(403)
            ->assertJson(['message' => 'You do not have permission to delete this reply.']);

        $this->assertDatabaseHas('replies', [
            'user_id' => $reply->user_id,
            'body' => $reply->body,
            'thread_id' => $reply->thread_id
        ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_delete_any_replies()
    {
        \Illuminate\Support\Facades\Auth::logout();

        $reply = Reply::factory()->create([
            'thread_id' => $this->thread->id,
        ]);

        $this->delete(route('replies.destroy', $reply->id))
            ->assertStatus(302);

        $this->assertDatabaseHas('replies', [
            'user_id' => $reply->user_id,
            'body' => $reply->body,
            'thread_id' => $reply->thread_id
        ]);
    }

    /** @test */
    public function authenticated_user_cannot_delete_nonexistent_reply()
    {
        $this->delete(route('replies.destroy', 99999999))
            ->assertStatus(404);
    }

    /** @test */
    public function deleting_a_reply_also_deletes_its_favorites()
    {
        $reply = Reply::factory()->create([
            'user_id' => $this->user->id,
            'thread_id' => $this->thread->id,
        ]);

        $reply->favoritedBy()->attach($this->user->id);

        $this->delete(route('replies.destroy', $reply->id))
            ->assertStatus(200)
            ->assertJson(['message' => 'Reply deleted successfully.']);

        $this->assertDatabaseMissing('replies', [
            'id' => $reply->id
        ]);

        $this->assertDatabaseMissing('favorite_replies', [
            'reply_id' => $reply->id
        ]);
    }

    /** @test */
    public function authenticated_users_cannot_see_delete_option_on_other_profiles()
    {
        $otherUser = User::factory()->create();
        $reply = Reply::factory()->create([
            'user_id' => $otherUser->id,
            'thread_id' => $this->thread->id,
        ]);

        $this->get(route('profile.show', ['user' => $otherUser->name]))
            ->assertStatus(200)
            ->assertDontSeeText('Delete Reply');
    }

    /** @test */
    public function authenticated_user_can_delete_reply_from_profile_page()
    {
        $reply = Reply::factory()->create([
            'user_id' => $this->user->id,
            'thread_id' => $this->thread->id,
        ]);

        $this->from(route('profile.show', ['user' => $this->user->name]))
            ->delete(route('replies.destroy', $reply->id))
            ->assertStatus(200)
            ->assertJson(['message' => 'Reply deleted successfully.']);

        $this->assertDatabaseMissing('replies', [
            'id' => $reply->id
        ]);
    }
}
