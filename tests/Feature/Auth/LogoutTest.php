<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_ログアウトができる()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect(route('login'));
    }
}
