<?php

namespace Tests\Feature\Genres;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;

class GenresShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_ジャンルに紐づく書籍のみが表示される(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルAとジャンルBを作成
        $genreA = Genre::factory()->create();
        $genreB = Genre::factory()->create();

        // ジャンルAに紐づく書籍を2冊作成
        $book1 = Book::factory()->create([
            'user_id' => $user->id,
        ]);
        $book2 = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // ジャンルBにのみ紐づく書籍を作成
        $book3 = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // ジャンルAとジャンルBの両方に紐づく書籍を作成
        $book4 = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // ジャンルとの紐づけ
        $book1->genres()->attach($genreA->id);
        $book2->genres()->attach($genreA->id);
        $book3->genres()->attach($genreB->id);
        $book4->genres()->attach([$genreA->id, $genreB->id]);

        // ジャンルAの詳細画面にアクセス
        $response = $this->actingAs($user)
            ->get(route('genres.show', $genreA));

        // ステータスコードの確認
        $response->assertOk();

        // ジャンルAに紐づく書籍がすべて表示されることを確認
        $response->assertSee($book1->title);
        $response->assertSee($book2->title);
        $response->assertSee($book4->title);

        // ジャンルBにのみ紐づく書籍が表示されないことを確認
        $response->assertDontSee($book3->title);
    }

    public function test_書籍に紐づいていないジャンルの場合にメッセージが表示される(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍に紐づいていないジャンルを作成
        // 書籍に紐づけないことで、「書籍が紐づいていないジャンル」とする
        $genre = Genre::factory()->create([
            'name' => 'エッセイ',
        ]);

        // ジャンル詳細画面にアクセス
        $response = $this->actingAs($user)
            ->get(route('genres.show', $genre));

        // ジャンル詳細画面が表示されることを確認
        $response->assertOk();

        // クリックしたジャンル名が表示されることを確認
        $response->assertSee('ジャンル: エッセイ');

        // 指定されたメッセージが表示されることを確認
        $response->assertSee('このジャンルの書籍はまだ登録されていません。');
    }
}
