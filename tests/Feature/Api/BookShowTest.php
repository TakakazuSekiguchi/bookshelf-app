<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;

class BookShowTest extends TestCase
{
    use RefreshDatabase;

    // 正常系
    public function test_書籍詳細を取得できる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // APIにリクエスト
        $response = $this->getJson('/api/v1/books/' . $book->id);

        // HTTPステータス200を確認
        $response->assertOk();

        // 書籍の基本情報が含まれることを確認
        $response->assertJsonPath('data.id', $book->id);
        $response->assertJsonPath('data.title', $book->title);
        $response->assertJsonPath('data.author', $book->author);
        $response->assertJsonPath('data.isbn', $book->isbn);
        $response->assertJsonPath('data.published_date', $book->published_date->format('Y-m-d'));
        $response->assertJsonPath('data.description', $book->description);
        $response->assertJsonPath('data.image_url', $book->image_url);
    }

    public function test_書籍詳細にジャンル情報が含まれる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genres = Genre::factory()->count(2)->create();

        // 書籍を作成し、ジャンルを紐づける
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $book->genres()->attach($genres->pluck('id'));

        // APIにリクエスト
        $response = $this->getJson('/api/v1/books/' . $book->id);

        // ステータスコードの確認
        $response->assertOk();

        // ジャンル情報が含まれていることを確認
        $response->assertJsonCount(2, 'data.genres');

        // 各ジャンルの情報が含まれていることを確認
        foreach ($genres as $genre) {
            $response->assertJsonFragment([
                'id' => $genre->id,
                'name' => $genre->name,
            ]);
        }
    }

    public function test_書籍詳細に投稿者情報が含まれる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // APIにリクエスト
        $response = $this->getJson('/api/v1/books/' . $book->id);

        // ステータスコードの確認
        $response->assertOk();

        // 投稿者情報が含まれることを確認
        $response->assertJsonPath('data.user.id', $user->id);
        $response->assertJsonPath('data.user.name', $user->name);
    }

    public function test_書籍詳細にレビュー情報が含まれる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // レビュー投稿者を作成
        $reviewUser = User::factory()->create();

        // レビューを作成
        $review = Review::factory()->create([
            'user_id' => $reviewUser->id,
            'book_id' => $book->id,
        ]);

        // APIにリクエスト
        $response = $this->getJson('/api/v1/books/' . $book->id);

        // ステータスコードの確認
        $response->assertOk();

        // レビュー情報が含まれることを確認
        $response->assertJsonFragment([
            'id' => $review->id,
            'user_id' => $reviewUser->id,
        ]);
    }

    // 異常系
    public function test_存在しない書籍IDを指定すると404エラーになる(): void
    {
        // 存在しない書籍IDを指定してAPIにリクエスト
        $response = $this->getJson('/api/v1/books/99999');

        // 404エラーが返ることを確認
        $response->assertNotFound();
    }
}
