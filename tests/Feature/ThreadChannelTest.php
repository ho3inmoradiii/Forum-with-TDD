<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
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

    /** @test */
    public function unauthenticated_user_cannot_delete_any_threads()
    {
        $user = User::factory()->create();

        $thread = Thread::factory()->create();

        $this->delete(route('threads.destroy', ['thread' => $thread]))
            ->assertStatus(302);

        $this->assertDatabaseHas('threads', [
            'id' => $thread->id
        ]);
    }

    /** @test */
    public function authenticated_user_cannot_delete_nonexistent_thread()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->delete(route('threads.destroy', ['thread' => 999999999]))
            ->assertStatus(404);
    }

    /** @test */
    public function deleting_a_thread_also_deletes_its_replies_and_favorites()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $thread = Thread::factory()->create([
            'user_id' => $user->id
        ]);

        $replies = Reply::factory()->count(3)->create([
            'thread_id' => $thread->id,
        ]);

        foreach ($replies as $reply) {
            $reply->favoritedBy()->attach($user->id);
        }

        $this->delete(route('threads.destroy', $thread->id))
            ->assertStatus(200)
            ->assertJson(['message' => 'Thread deleted successfully.']);

        $this->assertDatabaseMissing('threads', [
            'id' => $thread->id
        ]);

        $this->assertDatabaseMissing('replies', [
            'thread_id' => $thread->id
        ]);

        $this->assertDatabaseMissing('favorite_replies', [
            'reply_id' => $replies->pluck('id')->all()
        ]);
    }

    /** @test */
    public function authenticated_users_can_see_the_delete_thread_option_on_their_profile_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $thread = Thread::factory()->create([
            'user_id' => $user->id
        ]);

        $response = $this->get(route('profile.show', $user->name));

        $response->assertStatus(200)
            ->assertSee('profile-threads');
    }

    /** @test */
    public function authenticated_users_cannot_see_delete_option_on_other_profiles()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $otherUser = User::factory()->create();
        $thread = Thread::factory()->create([
            'user_id' => $otherUser
        ]);

        $this->get(route('profile.show', ['user' => $otherUser->name]))
            ->assertStatus(200)
            ->assertDontSeeText('Delete Thread');
    }

    /** @test */
    public function authenticated_user_can_delete_thread_from_profile_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $thread = Thread::factory()->create([
            'user_id' => $user->id
        ]);

        $this->from(route('profile.show', ['user' => $user->name]))
            ->delete(route('threads.destroy', ['thread' => $thread->id]))
            ->assertStatus(200)
            ->assertJson(['message' => 'Thread deleted successfully.']);

        $this->assertDatabaseMissing('threads', [
            'id' => $thread->id
        ]);
    }
}
