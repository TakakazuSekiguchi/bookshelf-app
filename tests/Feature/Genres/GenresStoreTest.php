<?php

namespace Tests\Feature\Genres;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;

class GenresStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_ジャンル登録画面を表示できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('genres.create'));

        $response->assertOk();
    }

    public function test_ジャンルを正常に登録できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('genres.store'), [
                'name' => 'テストジャンル',
            ]);

        $response->assertRedirect(route('genres.index'));

        $this->assertDatabaseHas('genres', [
            'name' => 'テストジャンル',
        ]);
    }

    public function test_ジャンル名が未入力の場合は登録できない(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('genres.store'), [
                'name' => '',
            ]);

        // バリデーションエラー確認
        $response->assertSessionHasErrors('name');

        $this->assertDatabaseMissing('genres', [
            'name' => '',
        ]);
    }
}
