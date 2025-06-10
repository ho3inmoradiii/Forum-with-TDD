<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use App\Notifications\NewReplyNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SubscribeThreadsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper method to sign in a user for tests.
     * If no user is provided, a new one will be created.
     */
    protected function signIn(User $user = null): User
    {
        $user = $user ?: User::factory()->create();
        $this->actingAs($user);
        return $user;
    }

    // ===================================================================
    // Guest User Tests
    // ===================================================================

    /** @test */
    public function guests_cannot_subscribe_to_threads()
    {
        $thread = Thread::factory()->create();

        $this->post(route('subscribe.thread.store', $thread))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function guests_cannot_unsubscribe_from_threads()
    {
        $thread = Thread::factory()->create();
        // Create a subscription for a user to ensure there is something to (not) delete
        $thread->subscribers()->attach(User::factory()->create());

        $this->delete(route('subscribe.thread.delete', $thread))
            ->assertRedirect(route('login'));
    }

    // ===================================================================
    // Authenticated User Subscription Logic
    // ===================================================================

    /** @test */
    public function an_authenticated_user_can_subscribe_to_a_thread()
    {
        $user = $this->signIn();
        $thread = Thread::factory()->create();

        $this->post(route('subscribe.thread.store', $thread))
            ->assertStatus(201)
            ->assertJson(['message' => 'Thread Subscribed']);

        $this->assertDatabaseHas('subscribe_threads', [
            'user_id' => $user->id,
            'thread_id' => $thread->id
        ]);
    }

    /** @test */
    public function an_authenticated_user_can_unsubscribe_from_a_thread()
    {
        $user = $this->signIn();
        $thread = Thread::factory()->create();
        $thread->subscribers()->attach($user->id);

        $this->delete(route('subscribe.thread.delete', $thread))
            ->assertStatus(200)
            ->assertJson(['message' => 'Thread Subscription deleted successfully']);

        $this->assertDatabaseMissing('subscribe_threads', [
            'user_id' => $user->id,
            'thread_id' => $thread->id
        ]);
    }

    /** @test */
    public function a_user_cannot_subscribe_to_a_thread_they_are_already_subscribed_to()
    {
        $user = $this->signIn();
        $thread = Thread::factory()->create();
        $thread->subscribers()->attach($user->id);

        $this->post(route('subscribe.thread.store', $thread))
            ->assertStatus(422)
            ->assertJson(['message' => 'Thread already subscribed']);

        $this->assertDatabaseCount('subscribe_threads', 1);
    }

    /** @test */
    public function a_user_cannot_unsubscribe_from_a_thread_they_are_not_subscribed_to()
    {
        $user = $this->signIn();
        $thread = Thread::factory()->create();

        $this->delete(route('subscribe.thread.delete', $thread))
            ->assertStatus(422)
            ->assertJson(['message' => 'Thread was not subscribed']);
    }

    // ===================================================================
    // Notification Logic Tests
    // ===================================================================

    /** @test */
    public function subscribers_are_notified_when_new_replies_are_added()
    {
        Notification::fake();

        // Arrange: A subscriber exists for a thread
        $subscriber = User::factory()->create();
        $thread = Thread::factory()->create();
        $thread->subscribers()->attach($subscriber->id);

        // Act: Two other users post replies
        $this->signIn(User::factory()->create());
        $this->postJson(route('replies.store', [$thread->channel, $thread]), ['body' => 'First reply']);

        $this->signIn(User::factory()->create());
        $this->postJson(route('replies.store', [$thread->channel, $thread]), ['body' => 'Second reply']);

        // Assert: The original subscriber received two notifications
        Notification::assertSentToTimes($subscriber, NewReplyNotification::class, 2);
    }

    /** @test */
    public function a_subscriber_is_not_notified_of_their_own_reply()
    {
        Notification::fake();

        // Arrange: A user subscribes to a thread
        $user = $this->signIn();
        $thread = Thread::factory()->create();
        $thread->subscribers()->attach($user->id);

        // Act: The same user posts a reply
        $this->postJson(route('replies.store', [$thread->channel, $thread]), ['body' => 'This is my own reply']);

        // Assert: No notification was sent to that user
        Notification::assertNotSentTo($user, NewReplyNotification::class);
    }

    // ===================================================================
    // UI, Navigation & Edge Case Tests
    // ===================================================================

    /** @test */
    public function an_authenticated_user_can_see_their_unread_notifications_in_the_ui()
    {
        // Arrange: A user subscribes to a thread, and another user replies
        $user = User::factory()->create();
        $thread = Thread::factory()->create();
        $thread->subscribers()->attach($user->id);

        $replyAuthor = User::factory()->create();
        $this->signIn($replyAuthor);
        $this->postJson(route('replies.store', [$thread->channel, $thread]), ['body' => 'A reply to trigger a notification.']);

        // Act: The subscribed user logs in and visits a page
        $this->signIn($user);
        $notification = $user->unreadNotifications->first();

        $response = $this->get(route('dashboard'));

        // Assert: The notification details are visible in the UI
        $response->assertOk()
            ->assertSee("New reply added to '" . $thread->title . "'")
            ->assertSee(route('notifications.read', ['notification' => $notification->id]));
    }

    /** @test */
    public function a_user_can_mark_a_notification_as_read_by_visiting_a_thread()
    {
        // Arrange: A subscriber, a thread, and another user to reply
        $user = User::factory()->create();
        $thread = Thread::factory()->create();
        $thread->subscribers()->attach($user->id);

        $replyAuthor = User::factory()->create();
        $this->signIn($replyAuthor);
        $this->postJson(route('replies.store', [$thread->channel, $thread]), ['body' => 'A reply to trigger a notification.']);

        $this->signIn($user);

        // Assert: User has one unread notification
        $this->assertCount(1, $user->unreadNotifications);
        $notificationId = $user->unreadNotifications->first()->id;

        // Act: User visits the thread page with the notification_id in the query string
        $this->get(route('threads.show', [$thread->channel, $thread, 'notification_id' => $notificationId]))
            ->assertOk();

        // Assert: The notification count is now zero
        $this->assertCount(0, $user->fresh()->unreadNotifications);
    }

    /** @test */
    public function it_returns_404_when_acting_on_non_existent_resources()
    {
        // Note: This test covers multiple, unrelated endpoints.
        // It might be better to split this into relevant test files.
        $this->signIn();

        // Try to act on a thread that doesn't exist
        $this->delete(route('subscribe.thread.delete', 999))->assertNotFound();

        // Try to act on a reply that doesn't exist
        $this->post(route('reply.favorite.store', 999))->assertNotFound();
    }
}
