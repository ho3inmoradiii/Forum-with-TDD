<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Channel;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ActivitiesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_records_an_activity_when_a_thread_is_created()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $thread = Thread::factory()->create([
            'user_id' => $user->id
        ]);

        $this->assertDatabaseCount('activities', 1);

        $this->assertDatabaseHas('activities', [
            'user_id' => $user->id,
            'activity_type' => 'thread_created',
            'target_id' => $thread->id,
            'target_type' => 'App\Models\Thread'
        ]);
    }

    /** @test */
    public function test_activity_relationships_are_correctly_associated()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $thread = Thread::factory()->create([
            'user_id' => $user->id
        ]);

        $activity = Activity::first();
        $this->assertEquals($activity->target_id, $thread->id);
        $this->assertEquals($activity->user_id, $user->id);
    }

    /** @test */
    public function test_no_activity_recorded_when_thread_fails_to_save()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $channel = Channel::factory()->create();

        $thread = Thread::factory()->raw([
            'title' => '',
            'channel_id' => $channel->id
        ]);

        $response = $this->postJson(route('threads.store'), $thread);

        $response->assertStatus(422);

        $this->assertDatabaseCount('threads', 0);
        $this->assertDatabaseCount('activities', 0);
    }

    /** @test */
    public function test_multiple_threads_create_multiple_activity_records()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $thread = Thread::factory()->create([
            'user_id' => $user->id
        ]);
        $anotherThread = Thread::factory()->create([
            'user_id' => $user->id
        ]);

        $this->assertDatabaseCount('activities', 2);

        $this->assertDatabaseHas('activities', [
            'user_id' => $user->id,
            'activity_type' => 'thread_created',
            'target_id' => $thread->id,
            'target_type' => 'App\Models\Thread'
        ]);

        $this->assertDatabaseHas('activities', [
            'user_id' => $user->id,
            'activity_type' => 'thread_created',
            'target_id' => $anotherThread->id,
            'target_type' => 'App\Models\Thread'
        ]);
    }

    /** @test */
    public function test_no_activity_recorded_for_unauthenticated_user_thread_creation()
    {
        $user = User::factory()->create();
        $channel = Channel::factory()->create();
        $thread = Thread::factory()->raw([
            'channel_id' => $channel->id
        ]);

        $response = $this->postJson(route('threads.store'), $thread);
        $response->assertStatus(401);

        $this->assertDatabaseCount('activities', 0);
    }

    /** @test */
    public function test_activities_are_ordered_from_newest_to_oldest()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $thread = Thread::factory()->create([
            'user_id' => $user->id,
        ]);

        $anotherThread = Thread::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subSeconds(10)
        ]);

        $lastActivity = Activity::latest()->first();
        $secondActivity = Activity::latest()->skip(1)->first();

        $this->assertEquals($lastActivity->target_id, $thread->id);
        $this->assertEquals($secondActivity->target_id, $anotherThread->id);
    }


}
