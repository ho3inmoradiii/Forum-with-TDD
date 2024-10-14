<?php

namespace Tests\Feature;

use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThreadsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_can_view_threads_list()
    {
        $thread = Thread::factory()->create();

        $response = $this->get('/threads');
        $response->assertStatus(200);
        $response->assertSee($thread->title);
    }

    /** @test */
    public function thread_titles_are_links_to_thread_pages()
    {
        $thread = Thread::factory()->create();

        $response = $this->get('/threads');

        $response->assertStatus(200);

        $response->assertSee($thread->title, false);
        $response->assertSee(route('threads.show', $thread), false);

        $response = $this->get(route('threads.show', $thread));
        $response->assertStatus(200);
        $response->assertSee($thread->title, false);
    }

    /** @test */
    public function guest_can_view_single_thread()
    {
        $thread = Thread::factory()->create();

        $response = $this->get("/threads/{$thread->id}");

        $response->assertStatus(200);
        $response->assertSee($thread->title);
        $response->assertSee($thread->body);
    }
}
