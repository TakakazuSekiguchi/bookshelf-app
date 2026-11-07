<?php

namespace Tests\Feature\Genres;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;

class GenresUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_ジャンル編集画面を表示できる(): void
    {
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('genres.edit', $genre));

        $response->assertOk();
    }

    public function test_ジャンルを正常に編集できる(): void
    {
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create([
            'name' => '変更前ジャンル',
        ]);

        // 更新するジャンル情報
        $updateData = [
            'name' => '変更後ジャンル',
        ];

        // ログインしてジャンルを更新
        $response = $this->actingAs($user)
            ->put(route('genres.update', $genre), $updateData);

        $response->assertRedirect(route('genres.index', $genre));

        // ジャンル名が更新されたことを確認
        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '変更後ジャンル',
        ]);

        // 変更前のジャンルがDBに残っていないことを確認
        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
            'name' => '変更前ジャンル',
        ]);
    }

    public function test_ジャンル名が未入力の場合は編集できない(): void
    {
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create([
            'name' => '変更前ジャンル',
        ]);

        // 更新するジャンル情報
        $updateData = [
            'name' => '',
        ];

        // ログインしてジャンルを更新
        $response = $this->actingAs($user)
            ->put(route('genres.update', $genre), $updateData);

        // バリデーションエラー確認
        $response->assertSessionHasErrors('name');

        // 空白では更新がされないことを確認
        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
            'name' => '',
        ]);

        // 変更前のジャンルがDBに残っていることを確認
        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '変更前ジャンル',
        ]);
    }

    public function test_登録済みのジャンル名には編集できない(): void
    {
        $user = User::factory()->create();

        // ジャンルを2つ作成
        $genre = Genre::factory()->create([
            'name' => '変更前ジャンル',
        ]);
        Genre::factory()->create([
            'name' => '登録済みジャンル',
        ]);

        // 更新するジャンル情報
        $updateData = [
            'name' => '登録済みジャンル',
        ];

        // ログインしてジャンルを更新
        $response = $this->actingAs($user)
            ->put(route('genres.update', $genre), $updateData);

        // バリデーションエラー確認
        $response->assertSessionHasErrors('name');

        // 変更前のジャンルがDBに残っていることを確認
        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '変更前ジャンル',
        ]);
    }
}
