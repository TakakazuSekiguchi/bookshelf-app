<?php

namespace Tests\Feature\Genres;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;

class GenresDestroyTest extends TestCase
{
    use RefreshDatabase;

    public function test_書籍に紐づいていないジャンルは正常に削除できる(): void
    {
        // ユーザーとジャンルを作成
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        // ログインユーザーとしてログイン
        $this->actingAs($user);

        // 削除リクエスト
        $response = $this->delete(route('genres.destroy', $genre));

        // ジャンル一覧画面へリダイレクトされること
        $response->assertRedirect(route('genres.index'));

        // genresテーブルにデータが保存されていないことを確認
        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
        ]);
    }

    public function test_未ログインの場合、ジャンルを削除できない(): void
    {
        // ユーザーと書籍を作成
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        // 削除リクエスト
        $response = $this->delete(route('genres.destroy', $genre));

        // ログイン画面へリダイレクトされること
        $response->assertRedirect(route('login'));

        // genresテーブルにデータが残っていることを確認
        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
        ]);
    }

    public function test_書籍に紐づいているジャンルは削除できない(): void
    {
        // ユーザーとジャンルを作成
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        // 書籍を作成、ジャンルを紐づけ
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);
        $book->genres()->attach($genre->id);

        // ログインユーザーとしてログイン
        $this->actingAs($user);

        // 削除リクエスト
        $response = $this->delete(route('genres.destroy', $genre));

        // リダイレクトされることを確認
        $response->assertRedirect(route('genres.index'));

        // genresテーブルにデータが残っていることを確認
        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
        ]);
    }
}
