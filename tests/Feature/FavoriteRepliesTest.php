<?php

namespace Tests\Feature;

use App\Models\Reply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FavoriteRepliesTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $reply;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->reply = Reply::factory()->create();
    }

    /** @test */
    public function an_authenticated_user_can_add_a_reply_to_their_favorites()
    {
        $user = $this->user;
        $this->actingAs($user);
        $reply = $this->reply;
        $response = $this->post(route('reply.favorite.store', $reply));
        $response->assertStatus(201)->assertJson(['message' => 'Reply favorited']);
        $this->assertDatabaseHas('favorite_replies', [
            'user_id' => $user->id,
            'reply_id' => $reply->id
        ]);
    }

    /** @test */
    public function an_authenticated_user_can_remove_the_reply_from_favorites()
    {
        $user = $this->user;
        $this->actingAs($user);

        $reply = $this->reply;
        $reply->favoritedBy()->attach($user->id);

        $response = $this->delete(route('reply.favorite.delete', $reply));
        $response->assertStatus(200)->assertJson(['message' => 'Reply Favorite deleted successfully']);

        $this->assertDatabaseMissing('favorite_replies', [
            'user_id' => $user->id,
            'reply_id' => $reply->id
        ]);
    }

    /** @test */
    public function an_authenticated_user_tries_to_re_favorite_a_reply_that_they_previously_favorited()
    {
        $user = $this->user;
        $this->actingAs($user);

        $reply = $this->reply;
        $reply->favoritedBy()->attach($user->id);

        $response = $this->post(route('reply.favorite.store', $reply));
        $response->assertStatus(422)->assertJson(['message' => 'Reply already favorited']);

        $this->assertDatabaseHas('favorite_replies', [
            'user_id' => $user->id,
            'reply_id' => $reply->id
        ])->assertDatabaseCount('favorite_replies', 1);
    }

    /** @test */
    public function an_authenticated_user_tries_to_delete_a_reply_that_they_did_not_favorite()
    {
        $user = $this->user;
        $this->actingAs($user);

        $reply = $this->reply;

        $response = $this->delete(route('reply.favorite.delete', $reply));
        $response->assertStatus(422)->assertJson(['message' => 'Reply was not favorited']);

        $this->assertDatabaseMissing('favorite_replies', [
            'user_id' => $user->id,
            'reply_id' => $reply->id
        ])->assertDatabaseCount('favorite_replies', 0);
    }

    /** @test */
    public function a_user_who_is_not_logged_in_cannot_favorite_a_reply()
    {
        $user = $this->user;
        $reply = $this->reply;

        $response = $this->post(route('reply.favorite.store', $reply));
        $response->assertStatus(302)->assertRedirect('/login');

        $this->assertDatabaseMissing('favorite_replies', [
            'user_id' => $user->id,
            'reply_id' => $reply->id
        ])->assertDatabaseCount('favorite_replies', 0);
    }

    /** @test */
    public function a_user_cannot_remove_a_reply_from_favorites_without_logging_in()
    {
        $user = $this->user;
        $reply = $this->reply;

        $reply->favoritedBy()->attach($user->id);

        $response = $this->delete(route('reply.favorite.delete', $reply));
        $response->assertStatus(302)->assertRedirect('/login');

        $this->assertDatabaseHas('favorite_replies', [
            'user_id' => $user->id,
            'reply_id' => $reply->id
        ])->assertDatabaseCount('favorite_replies', 1);
    }

    /** @test */
    public function a_user_cannot_remove_another_user_s_reply_from_their_favorites()
    {
        $user1 = $this->user;
        $user2 = User::factory()->create();

        $this->actingAs($user1);

        $reply = $this->reply;

        $reply->favoritedBy()->attach($user2->id);

        $response = $this->delete(route('reply.favorite.delete', $reply));
        $response->assertStatus(422)->assertJson(['message' => 'Reply was not favorited']);

        $this->assertDatabaseHas('favorite_replies', [
            'user_id' => $user2->id,
            'reply_id' => $reply->id
        ])->assertDatabaseCount('favorite_replies', 1);
    }

    /** @test */
    public function a_user_wants_to_favorite_or_delete_a_reply_that_does_not_exist()
    {
        $user = $this->user;

        $this->actingAs($user);

        $response1 = $this->delete(route('reply.favorite.delete', 999));
        $response1->assertStatus(404);

        $response2 = $this->post(route('reply.favorite.store', 999));
        $response2->assertStatus(404);
    }
}
