<?php

namespace Tests\Feature\Genres;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;

class GenresIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_登録されたジャンル名が表示される(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create([
            'name' => '技術書',
        ]);

        // ジャンル一覧画面にアクセス
        $response = $this->actingAs($user)
            ->get(route('genres.index'));

        // レスポンスとジャンル名を確認
        $response->assertOk()->assertSee('技術書');
    }

    public function test_ジャンルに紐づく書籍数が正しく表示される(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create([
            'name' => '技術書',
        ]);

        // 書籍を3件作成し、ジャンルに紐づける
        $books = Book::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        $genre->books()->attach($books->pluck('id'));

        // ジャンル一覧画面にアクセス
        $response = $this->actingAs($user)
            ->get(route('genres.index'));

        // ジャンルに紐づく書籍数が表示されることを確認
        $response->assertOk()
            ->assertSee('技術書')
            ->assertSee('3');
    }

    public function test_書籍が紐づいていないジャンルは書籍数が0冊と表示される(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        // 書籍に紐づけないことで、「書籍が紐づいていないジャンル」とする
        $genre = Genre::factory()->create([
            'name' => '料理',
        ]);

        // ジャンル一覧画面にアクセス
        $response = $this->actingAs($user)
            ->get(route('genres.index'));

        // ジャンル名の表示、書籍数が0冊と表示されることを確認
        $response->assertOk()
            ->assertSee('料理')
            ->assertSee('0');
    }

    public function test_未ログインの場合はログイン画面にリダイレクトされる(): void
    {
        // 未ログイン状態でジャンル一覧画面にアクセス
        $response = $this->get(route('genres.index'));

        // ログイン画面にリダイレクトされることを確認
        $response->assertRedirect(route('login'));
    }
}
