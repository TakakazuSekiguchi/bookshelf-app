<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;

class BookSearchTest extends TestCase
{
    use RefreshDatabase;

    // // 正常系
    public function test_書籍タイトルで検索できる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 検索対象の書籍を作成
        $targetBook = Book::factory()->create([
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'user_id' => $user->id,
        ]);

        // 検索対象外の書籍を作成
        Book::factory()->create([
            'title' => 'PHP入門',
            'author' => '佐藤花子',
            'user_id' => $user->id,
        ]);

        // 書籍タイトルで検索
        $response = $this->getJson('/api/v1/books?keyword=Laravel');

        // 正常にレスポンスが返されること
        $response->assertOk();

        // 検索対象の書籍が含まれること
        $response->assertJsonFragment([
            'title' => $targetBook->title,
            'author' => $targetBook->author,
        ]);

        // 検索対象外の書籍が含まれないこと
        $response->assertJsonMissing([
            'title' => 'PHP入門',
            'author' => '佐藤花子',
        ]);
    }

    public function test_著者名で検索できる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 検索対象の書籍を作成
        $targetBook = Book::factory()->create([
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'user_id' => $user->id,
        ]);

        // 検索対象外の書籍を作成
        Book::factory()->create([
            'title' => 'PHP入門',
            'author' => '佐藤花子',
            'user_id' => $user->id,
        ]);

        // 書籍タイトルで検索
        $response = $this->getJson('/api/v1/books?keyword=山田太郎');

        // 正常にレスポンスが返されること
        $response->assertOk();

        // 検索対象の書籍が含まれること
        $response->assertJsonFragment([
            'title' => $targetBook->title,
            'author' => $targetBook->author,
        ]);

        // 検索対象外の書籍が含まれないこと
        $response->assertJsonMissing([
            'title' => 'PHP入門',
            'author' => '佐藤花子',
        ]);
    }

    public function test_キーワードの部分一致で検索できる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // タイトルの部分一致で検索される書籍
        $titleMatchBook = Book::factory()->create([
            'title' => 'Laravel実践入門',
            'author' => '山田太郎',
            'user_id' => $user->id,
        ]);

        // 著者名の部分一致で検索される書籍
        $authorMatchBook = Book::factory()->create([
            'title' => 'PHP入門',
            'author' => 'Laravel太郎',
            'user_id' => $user->id,
        ]);

        // 検索対象外の書籍
        Book::factory()->create([
            'title' => 'Python入門',
            'author' => '佐藤花子',
            'user_id' => $user->id,
        ]);

        // 部分一致するキーワードで検索
        $response = $this->getJson('/api/v1/books?keyword=Laravel');

        // 正常にレスポンスが返されること
        $response->assertOk();

        // タイトルの部分一致で検索できること
        $response->assertJsonFragment([
            'title' => $titleMatchBook->title,
        ]);

        // 著者名の部分一致で検索できること
        $response->assertJsonFragment([
            'title' => $authorMatchBook->title,
        ]);

        // 検索対象外の書籍が含まれないこと
        $response->assertJsonMissing([
            'title' => 'Python入門',
        ]);
    }

    public function test_ジャンルIDで絞り込み検索できる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $targetGenre = Genre::factory()->create([
            'name' => '小説',
        ]);

        $otherGenre = Genre::factory()->create([
            'name' => 'ビジネス',
        ]);

        // 検索対象の書籍を作成
        $targetBook = Book::factory()->create([
            'title' => 'Laravel入門',
            'user_id' => $user->id,
        ]);

        // 検索対象外の書籍を作成
        $otherBook = Book::factory()->create([
            'title' => 'PHP入門',
            'user_id' => $user->id,
        ]);

        // 書籍とジャンルを紐づける
        $targetBook->genres()->attach($targetGenre->id);
        $otherBook->genres()->attach($otherGenre->id);

        // ジャンルIDで絞り込み検索
        $response = $this->getJson(
            '/api/v1/books?genre=' . $targetGenre->id
        );

        // 正常にレスポンスが返されること
        $response->assertOk();

        // 指定したジャンルの書籍が含まれること
        $response->assertJsonFragment([
            'title' => $targetBook->title,
        ]);

        // 指定していないジャンルの書籍が含まれないこと
        $response->assertJsonMissing([
            'title' => $otherBook->title,
        ]);
    }

    public function test_キーワードとジャンルIDを組み合わせて検索できる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $targetGenre = Genre::factory()->create([
            'name' => '小説',
        ]);

        $otherGenre = Genre::factory()->create([
            'name' => 'ビジネス',
        ]);

        // キーワードとジャンルの両方に一致する書籍
        $targetBook = Book::factory()->create([
            'title' => 'Laravel実践入門',
            'author' => '山田太郎',
            'user_id' => $user->id,
        ]);

        // キーワードには一致するが、ジャンルが異なる書籍
        $differentGenreBook = Book::factory()->create([
            'title' => 'Laravelビジネス活用',
            'author' => '佐藤花子',
            'user_id' => $user->id,
        ]);

        // ジャンルには一致するが、キーワードが異なる書籍
        $differentKeywordBook = Book::factory()->create([
            'title' => 'PHP入門',
            'author' => '鈴木一郎',
            'user_id' => $user->id,
        ]);

        // 書籍とジャンルを紐づける
        $targetBook->genres()->attach($targetGenre->id);
        $differentGenreBook->genres()->attach($otherGenre->id);
        $differentKeywordBook->genres()->attach($targetGenre->id);

        // キーワードとジャンルIDを組み合わせて検索
        $response = $this->getJson(
            '/api/v1/books?keyword=Laravel&genre=' . $targetGenre->id
        );

        // 正常にレスポンスが返されること
        $response->assertOk();

        // 両方の条件に一致する書籍が含まれること
        $response->assertJsonFragment([
            'title' => $targetBook->title,
        ]);

        // キーワードだけ一致する書籍が含まれないこと
        $response->assertJsonMissing([
            'title' => $differentGenreBook->title,
        ]);

        // ジャンルだけ一致する書籍が含まれないこと
        $response->assertJsonMissing([
            'title' => $differentKeywordBook->title,
        ]);
    }

    public function test_検索結果が0件の場合に空の配列を返す(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        Book::factory()->create([
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'user_id' => $user->id,
        ]);

        // 存在しないキーワードで検索
        $response = $this->getJson(
            '/api/v1/books?keyword=存在しない書籍'
        );

        // 正常にレスポンスが返されること
        $response->assertOk();

        // 検索結果が空の配列であること
        $response->assertJsonPath('data', []);
    }

    public function test_検索結果が10件ずつ取得できる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 検索条件に一致する書籍を11件作成
        Book::factory()->count(11)->create([
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'user_id' => $user->id,
        ]);

        // キーワードで検索
        $response = $this->getJson('/api/v1/books?keyword=Laravel');

        // 正常にレスポンスが返されること
        $response->assertOk();

        // 取得件数が10件であること
        $response->assertJsonCount(10, 'data');

        // 全検索結果が11件であること
        $response->assertJsonPath('meta.total', 11);
    }

    public function test_検索結果の2ページ目を取得できる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 検索条件に一致する書籍を11件作成
        Book::factory()->count(11)->create([
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'user_id' => $user->id,
        ]);

        // 検索結果の2ページ目を取得
        $response = $this->getJson('/api/v1/books?keyword=Laravel&page=2');

        // 正常にレスポンスが返されること
        $response->assertOk();

        // 2ページ目の取得件数が1件であること
        $response->assertJsonCount(1, 'data');

        // 全検索結果が11件であること
        $response->assertJsonPath('meta.total', 11);

        // 現在のページが2ページ目であること
        $response->assertJsonPath('meta.current_page', 2);
    }

    public function test_存在しないジャンルIDを指定した場合に検索結果が0件になる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create([
            'name' => '小説',
        ]);

        // 書籍を作成
        $book = Book::factory()->create([
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'user_id' => $user->id,
        ]);

        // 書籍とジャンルを紐づける
        $book->genres()->attach($genre->id);

        // 存在しないジャンルIDを指定して検索
        $response = $this->getJson('/api/v1/books?genre=9999');

        // 正常にレスポンスが返されること
        $response->assertOk();

        // 検索結果が空の配列であること
        $response->assertJsonPath('data', []);

        // 検索結果の総件数が0件であること
        $response->assertJsonPath('meta.total', 0);
    }
}
