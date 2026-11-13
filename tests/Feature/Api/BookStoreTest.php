<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;

class BookStoreTest extends TestCase
{
    use RefreshDatabase;

    // 正常系
    public function test_書籍を登録できる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // Sanctumのトークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        // ジャンルを作成
        $genres = Genre::factory()->count(2)->create();

        // 書籍登録APIにアクセス
        $response = $this->withToken($token)
            ->postJson('/api/v1/books', [
                'title' => 'テスト書籍',
                'author' => 'テスト著者',
                'isbn' => '1234567890123',
                'published_date' => '2025-01-01',
                'genres' => $genres->pluck('id')->toArray(),
            ]);

        // レスポンスの確認
        $response->assertCreated();

        // JSONレスポンスの確認
        $response->assertJsonPath('data.title', 'テスト書籍');
        $response->assertJsonPath('data.author', 'テスト著者');

        // データベースの確認
        $this->assertDatabaseHas('books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'user_id' => $user->id,
        ]);
    }

    public function test_複数のジャンルを指定して書籍を登録できる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // Sanctumのトークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        // ジャンルを作成
        $genres = Genre::factory()->count(2)->create();

        // 書籍登録APIにアクセス
        $response = $this->withToken($token)
            ->postJson('/api/v1/books', [
                'title' => 'テスト書籍',
                'author' => 'テスト著者',
                'isbn' => '1234567890123',
                'published_date' => '2025-01-01',
                'genres' => $genres->pluck('id')->toArray(),
            ]);

        // 登録成功を確認
        $response->assertCreated();

        // 登録した書籍を取得
        $book = Book::where('isbn', '1234567890123')->firstOrFail();

        // 中間テーブルにジャンルが登録されていることを確認
        foreach ($genres as $genre) {
            $this->assertDatabaseHas('book_genre', [
                'book_id' => $book->id,
                'genre_id' => $genre->id,
            ]);
        }
    }

    // 異常系
    public function test_タイトルが未入力の場合は登録できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // Sanctumのトークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        // ジャンルを作成
        $genres = Genre::factory()->count(2)->create();

        // 書籍登録APIにアクセス
        $response = $this->withToken($token)
            ->postJson('/api/v1/books', [
                'author' => 'テスト著者',
                'isbn' => '1234567890123',
                'published_date' => '2025-01-01',
                'genres' => $genres->pluck('id')->toArray(),
            ]);

        // バリデーションエラーを確認
        $response->assertUnprocessable();

        // titleのエラーを確認
        $response->assertJsonValidationErrors(['title']);

        // 書籍が登録されていないことを確認
        $this->assertDatabaseMissing('books', [
            'isbn' => '1234567890123',
        ]);
    }

    public function test_著者が未入力の場合は登録できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // Sanctumのトークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        // ジャンルを作成
        $genres = Genre::factory()->count(2)->create();

        // 書籍登録APIにアクセス
        $response = $this->withToken($token)
            ->postJson('/api/v1/books', [
                'title' => 'テスト書籍',
                'isbn' => '1234567890123',
                'published_date' => '2025-01-01',
                'genres' => $genres->pluck('id')->toArray(),
            ]);

        // バリデーションエラーを確認
        $response->assertUnprocessable();

        // authorのエラーを確認
        $response->assertJsonValidationErrors(['author']);

        // 書籍が登録されていないことを確認
        $this->assertDatabaseMissing('books', [
            'isbn' => '1234567890123',
        ]);
    }

    public function test_ISBNが13桁以外の場合は登録できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // Sanctumのトークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        // ジャンルを作成
        $genres = Genre::factory()->count(2)->create();

        // 書籍登録APIにアクセス
        $response = $this->withToken($token)
            ->postJson('/api/v1/books', [
                'title' => 'テスト書籍',
                'author' => 'テスト著者',
                'isbn' => '123456789012', // 12桁
                'published_date' => '2025-01-01',
                'genres' => $genres->pluck('id')->toArray(),
            ]);

        // バリデーションエラーを確認
        $response->assertUnprocessable();

        // isbnのエラーを確認
        $response->assertJsonValidationErrors(['isbn']);

        // 書籍が登録されていないことを確認
        $this->assertDatabaseMissing('books', [
            'isbn' => '123456789012',
        ]);
    }

    public function test_登録済みのISBNは登録できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // Sanctumのトークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        // 既存の書籍を作成
        $book = Book::factory()->create([
            'isbn' => '1234567890123',
        ]);

        // ジャンルを作成
        $genres = Genre::factory()->count(2)->create();

        // 書籍登録APIにアクセス
        $response = $this->withToken($token)
            ->postJson('/api/v1/books', [
                'title' => 'テスト書籍',
                'author' => 'テスト著者',
                'isbn' => '1234567890123',
                'published_date' => '2025-01-01',
                'genres' => $genres->pluck('id')->toArray(),
            ]);

        // バリデーションエラーを確認
        $response->assertUnprocessable();

        // isbnのエラーを確認
        $response->assertJsonValidationErrors(['isbn']);

        // 既存の書籍が維持されていることを確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'isbn' => '1234567890123',
        ]);

        // 同じISBNの書籍が重複登録されていないことを確認
        $this->assertSame(
            1,
            Book::where('isbn', '1234567890123')->count()
        );
    }

    public function test_出版日が日付形式でない場合は登録できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // Sanctumのトークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        // ジャンルを作成
        $genres = Genre::factory()->count(2)->create();

        // 書籍登録APIにアクセス
        $response = $this->withToken($token)
            ->postJson('/api/v1/books', [
                'title' => 'テスト書籍',
                'author' => 'テスト著者',
                'isbn' => '1234567890123',
                'published_date' => '2025-13-45',
                'genres' => $genres->pluck('id')->toArray(),
            ]);

        // バリデーションエラーを確認
        $response->assertUnprocessable();

        // published_dateのエラーを確認
        $response->assertJsonValidationErrors(['published_date']);

        // 書籍が登録されていないことを確認
        $this->assertDatabaseMissing('books', [
            'isbn' => '1234567890123',
        ]);
    }

    public function test_ジャンルが未指定の場合は登録できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // Sanctumのトークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        // 書籍登録APIにアクセス
        $response = $this->withToken($token)
            ->postJson('/api/v1/books', [
                'title' => 'テスト書籍',
                'author' => 'テスト著者',
                'isbn' => '1234567890123',
                'published_date' => '2025-01-01',
            ]);

        // バリデーションエラーを確認
        $response->assertUnprocessable();

        // genresのエラーを確認
        $response->assertJsonValidationErrors(['genres']);

        // 書籍が登録されていないことを確認
        $this->assertDatabaseMissing('books', [
            'isbn' => '1234567890123',
        ]);
    }

    public function test_ジャンルが空の場合は登録できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // Sanctumのトークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        // 書籍登録APIにアクセス
        $response = $this->withToken($token)
            ->postJson('/api/v1/books', [
                'title' => 'テスト書籍',
                'author' => 'テスト著者',
                'isbn' => '1234567890123',
                'published_date' => '2025-01-01',
                'genres' => [],
            ]);

        // バリデーションエラーを確認
        $response->assertUnprocessable();

        // genresのエラーを確認
        $response->assertJsonValidationErrors(['genres']);

        // 書籍が登録されていないことを確認
        $this->assertDatabaseMissing('books', [
            'isbn' => '1234567890123',
        ]);
    }

    public function test_ジャンルIDが整数以外の場合は登録できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // Sanctumのトークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        // 書籍登録APIにアクセス
        $response = $this->withToken($token)
            ->postJson('/api/v1/books', [
                'title' => 'テスト書籍',
                'author' => 'テスト著者',
                'isbn' => '1234567890123',
                'published_date' => '2025-01-01',
                'genres' => ['abc'],
            ]);

        // バリデーションエラーを確認
        $response->assertUnprocessable();

        // genres.0のエラーを確認
        $response->assertJsonValidationErrors(['genres.0']);

        // 書籍が登録されていないことを確認
        $this->assertDatabaseMissing('books', [
            'isbn' => '1234567890123',
        ]);
    }

    public function test_存在しないジャンルIDでは登録できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // Sanctumのトークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        // 書籍登録APIにアクセス
        $response = $this->withToken($token)
            ->postJson('/api/v1/books', [
                'title' => 'テスト書籍',
                'author' => 'テスト著者',
                'isbn' => '1234567890123',
                'published_date' => '2025-01-01',
                'genres' => [999999],
            ]);

        // バリデーションエラーを確認
        $response->assertUnprocessable();

        // genres.0のエラーを確認
        $response->assertJsonValidationErrors(['genres.0']);

        // 書籍が登録されていないことを確認
        $this->assertDatabaseMissing('books', [
            'isbn' => '1234567890123',
        ]);
    }
}
