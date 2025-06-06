<?php

namespace Tests\Feature;

use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function a_user_has_profile()
    {
        $user = $this->user;

        $this->get(route('profile.show', ['user' => $user->name]))
            ->assertStatus(200)
            ->assertSee($user->name);
    }

    /** @test */
    public function profile_displays_all_threads_created_by_the_associated_user()
    {
        $user1 = $this->user;
        $user2 = User::factory()->create();

        $thread1 = Thread::factory()->create([
            'user_id' => $user1->id,
        ]);

        $thread2 = Thread::factory()->create([
            'user_id' => $user2->id,
        ]);

        $thread3 = Thread::factory()->create([
            'user_id' => $user1->id,
        ]);

        $this->get(route('profile.show', ['user' => $user1->name]))
            ->assertStatus(200)
            ->assertSee([$thread1->title, $thread3->title])
            ->assertDontSee($thread2->title);
    }

    /** @test */
    public function profile_page_displays_the_message_if_the_user_has_not_posted_any_threads()
    {
        $user = $this->user;
        $user1 = User::factory()->create();

        $thread = Thread::factory()->create([
            'user_id' => $user1->id
        ]);

        $this->get(route('profile.show', ['user' => $user->name]))
            ->assertStatus(200)
            ->assertSee('profile-activities');
    }

    /** @test */
    public function show_404_page_user_not_found_on_profile_page()
    {
        $this->get(route('profile.show', ['user' => 'sampleName123456789']))
            ->assertStatus(404);
    }
}
