<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;

class BookIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_書籍一覧を取得できる(): void
    {
        // 書籍を作成
        $books = Book::factory()->count(3)->create();

        // APIにリクエスト
        $response = $this->getJson('/api/v1/books');

        // HTTPステータスが200であること
        $response->assertOk();

        // 書籍が3件取得できること
        $response->assertJsonCount(3, 'data');

        // 作成した書籍がレスポンスに含まれていること
        foreach ($books as $book) {
            $response->assertJsonFragment([
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->author,
            ]);
        }
    }

    public function test_書籍一覧にジャンル情報が含まれる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create([
            'name' => '技術書',
        ]);

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 書籍にジャンルを紐づける
        $book->genres()->attach($genre->id);

        // 書籍一覧APIにリクエスト
        $response = $this->getJson('/api/v1/books');

        // HTTPステータスが200であること
        $response->assertOk();

        // ジャンル情報が正しく取得できること
        $response->assertJsonFragment([
            'id' => $genre->id,
            'name' => $genre->name,
        ]);
    }

    public function test_書籍一覧に平均評価が含まれる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // レビューを2件作成
        Review::factory()->create([
            'book_id' => $book->id,
            'rating' => 4,
        ]);

        Review::factory()->create([
            'book_id' => $book->id,
            'rating' => 2,
        ]);

        // 書籍一覧APIにリクエスト
        $response = $this->getJson('/api/v1/books');

        // HTTPステータスが200であること
        $response->assertOk();

        // 平均評価が正しく取得できること（4 + 2）÷ 2 = 3
        $response->assertJsonPath('data.0.average_rating', '3.0000');
    }

    public function test_書籍一覧にレビュー件数が含まれる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // レビューを2件作成
        Review::factory()->count(2)->create([
            'book_id' => $book->id,
        ]);

        // 書籍一覧APIにリクエスト
        $response = $this->getJson('/api/v1/books');

        // HTTPステータスが200であること
        $response->assertOk();

        // レビュー件数が正しく取得できること
        $response->assertJsonPath('data.0.reviews_count', 2);
    }

    public function test_書籍一覧が10件ずつページネーションされる(): void
    {
        // 書籍を11件作成
        Book::factory()->count(11)->create();

        // 書籍一覧APIにリクエスト
        $response = $this->getJson('/api/v1/books');

        // HTTPステータスが200であること
        $response->assertOk();

        // 1ページ目の取得件数が10件であること
        $response->assertJsonCount(10, 'data');

        // meta内のページネーション情報を確認
        $response->assertJsonPath('meta.current_page', 1); // 現在のページ
        $response->assertJsonPath('meta.per_page', 10);    // 1ページあたりの取得件数
        $response->assertJsonPath('meta.total', 11);       // 書籍の総件数
        $response->assertJsonPath('meta.last_page', 2);    // 最終ページ
    }

    public function test_2ページ目の書籍一覧を取得できる(): void
    {
        // 書籍を11件作成
        Book::factory()->count(11)->create();

        // 2ページ目を指定してAPIにリクエスト
        $response = $this->getJson('/api/v1/books?page=2');

        // HTTPステータスが200であること
        $response->assertOk();

        // 2ページ目の取得件数が1件であること
        $response->assertJsonCount(1, 'data');

        // 2ページ目の書籍情報が正しく取得できること
        $response->assertJsonFragment([
            'title' => Book::latest('id')->first()->title,
            'author' => Book::latest('id')->first()->author,
        ]);

        // meta内のページネーション情報を確認
        $response->assertJsonPath('meta.current_page', 2);
        $response->assertJsonPath('meta.per_page', 10);
        $response->assertJsonPath('meta.total', 11);
        $response->assertJsonPath('meta.last_page', 2);
    }

    public function test_存在しないページ番号を指定した場合、空の書籍一覧が返される(): void
    {
        // 書籍を11件作成（全2ページ）
        Book::factory()->count(11)->create();

        // 存在しない3ページ目を指定してリクエスト
        $response = $this->getJson('/api/v1/books?page=3');

        // HTTPステータスが200であること
        $response->assertOk();

        // 書籍データが空であること
        $response->assertJsonCount(0, 'data');

        // meta内のページネーション情報を確認
        $response->assertJsonPath('meta.current_page', 3);
        $response->assertJsonPath('meta.per_page', 10);
        $response->assertJsonPath('meta.total', 11);
        $response->assertJsonPath('meta.last_page', 2);
    }
}
