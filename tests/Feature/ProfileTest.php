<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_has_profile()
    {
        $user = User::factory()->create();

        $this->get(route('profile.show', ['user' => $user->name]))
            ->assertStatus(200)
            ->assertSee($user->name);
    }
}
