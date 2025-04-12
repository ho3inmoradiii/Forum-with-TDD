<?php

namespace Tests\Feature;

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
}
