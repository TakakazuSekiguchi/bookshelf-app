<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;

class BookUpdateTest extends TestCase
{
    use RefreshDatabase;

    // 正常系
    public function test_書籍を正しく更新できる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'isbn' => '1111111111111',
        ]);

        // アクセストークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        // 更新する書籍情報
        $data = [
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => '2222222222222',
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ];

        // 書籍更新APIにアクセス
        $response = $this->withToken($token)
            ->putJson("/api/v1/books/{$book->id}", $data);

        // JSONレスポンスを確認
        $response->assertOk()
            ->assertJsonPath('data.title', '更新後のタイトル')
            ->assertJsonPath('data.author', '更新後の著者')
            ->assertJsonPath('data.isbn', '2222222222222')
            ->assertJsonPath('data.published_date', '2026-01-01');

        // DBの更新内容を確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => '2222222222222',
            'published_date' => '2026-01-01',
        ]);
    }

    public function test_自分自身のISBNと同じ値で更新できる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'isbn' => '1111111111111',
        ]);

        // アクセストークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        // 更新する書籍情報
        $data = [
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => '1111111111111', //元々のISBNのまま変更しない
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ];

        // 書籍更新APIにアクセス
        $response = $this->withToken($token)
            ->putJson("/api/v1/books/{$book->id}", $data);

        // JSONレスポンスを確認
        $response->assertOk()
            ->assertJsonPath('data.title', '更新後のタイトル')
            ->assertJsonPath('data.author', '更新後の著者')
            ->assertJsonPath('data.isbn', '1111111111111') //元々のISBNのまま変更されていない
            ->assertJsonPath('data.published_date', '2026-01-01');

        // DBの更新内容を確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => '1111111111111', //元々のISBNのまま変更されていない
            'published_date' => '2026-01-01',
        ]);
    }

    public function test_書籍のジャンルを正しく更新できる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genreA = Genre::factory()->create();
        $genreB = Genre::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 更新前のジャンルを紐付け
        $book->genres()->attach($genreA->id);

        // アクセストークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        // 更新する書籍情報
        $data = [
            'title' => $book->title,
            'author' => $book->author,
            'isbn' => $book->isbn,
            'published_date' => $book->published_date->format('Y-m-d'),
            'genres' => [$genreB->id],
        ];

        // 書籍更新APIにアクセス
        $response = $this->withToken($token)
            ->putJson("/api/v1/books/{$book->id}", $data);

        // JSONレスポンスを確認
        $response->assertOk()
            ->assertJsonPath('data.genres.0.id', $genreB->id)
            ->assertJsonPath('data.genres.0.name', $genreB->name);

        // 更新後のジャンルとの紐付けを確認
        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genreB->id,
        ]);

        // 更新前のジャンルとの紐付けが解除されていることを確認
        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genreA->id,
        ]);
    }

    // 認証・認可
    public function test_未認証ユーザーは書籍を更新できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 更新する書籍情報
        $data = [
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => '2222222222222',
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ];

        // 未認証ユーザーとして書籍更新APIにアクセス
        $response = $this->putJson("/api/v1/books/{$book->id}", $data);

        // 認証エラーを確認
        $response->assertUnauthorized();

        // 書籍情報が変更されていないことを確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => $book->title,
            'author' => $book->author,
            'isbn' => $book->isbn,
        ]);
    }

    public function test_他のユーザーが登録した書籍は更新できない(): void
    {
        // ユーザーを2人作成
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // ユーザーAの書籍を作成
        $book = Book::factory()->create([
            'user_id' => $userA->id,
        ]);

        // ユーザーBのアクセストークンを発行
        $token = $userB->createToken('test-token')->plainTextToken;

        // 更新する書籍情報
        $data = [
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => '2222222222222',
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ];

        // ユーザーBとして書籍更新APIにアクセス
        $response = $this->withToken($token)
            ->putJson("/api/v1/books/{$book->id}", $data);

        // 認可エラーを確認
        $response->assertForbidden();

        // 書籍情報が変更されていないことを確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'user_id' => $userA->id,
            'title' => $book->title,
            'author' => $book->author,
            'isbn' => $book->isbn,
        ]);
    }

    public function test_存在しない書籍は更新できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // アクセストークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        // 存在しない書籍ID
        $bookId = 999999;

        // 更新する書籍情報
        $data = [
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => '2222222222222',
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ];

        // 書籍更新APIにアクセス
        $response = $this->withToken($token)
            ->putJson("/api/v1/books/{$bookId}", $data);

        // 404エラーを確認
        $response->assertNotFound();
    }

    // バリデーション
    public function test_タイトルが未入力の場合は更新できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // アクセストークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        // 更新する書籍情報（タイトルを未入力にする）
        $data = [
            'title' => '',
            'author' => '更新後の著者',
            'isbn' => '2222222222222',
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ];

        // 書籍更新APIにアクセス
        $response = $this->withToken($token)
            ->putJson("/api/v1/books/{$book->id}", $data);

        // バリデーションエラーを確認
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);

        // 書籍情報が変更されていないことを確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => $book->title,
            'author' => $book->author,
            'isbn' => $book->isbn,
        ]);
    }

    public function test_著者が未入力の場合は更新できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // アクセストークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        // 更新する書籍情報（著者を未入力にする）
        $data = [
            'title' => '更新後のタイトル',
            'author' => '',
            'isbn' => '2222222222222',
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ];

        // 書籍更新APIにアクセス
        $response = $this->withToken($token)
            ->putJson("/api/v1/books/{$book->id}", $data);

        // バリデーションエラーを確認
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['author']);

        // 書籍情報が変更されていないことを確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => $book->title,
            'author' => $book->author,
            'isbn' => $book->isbn,
        ]);
    }

    public function test_ISBNが未入力の場合は更新できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // アクセストークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        // 更新する書籍情報（ISBNを未入力にする）
        $data = [
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => '',
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ];

        // 書籍更新APIにアクセス
        $response = $this->withToken($token)
            ->putJson("/api/v1/books/{$book->id}", $data);

        // バリデーションエラーを確認
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['isbn']);

        // 書籍情報が変更されていないことを確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => $book->title,
            'author' => $book->author,
            'isbn' => $book->isbn,
        ]);
    }

    public function test_ISBNが13桁以外の場合は更新できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // アクセストークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        // 更新する書籍情報（ISBNを12桁にする）
        $data = [
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => '222222222222',
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ];

        // 書籍更新APIにアクセス
        $response = $this->withToken($token)
            ->putJson("/api/v1/books/{$book->id}", $data);

        // バリデーションエラーを確認
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['isbn']);

        // 書籍情報が変更されていないことを確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => $book->title,
            'author' => $book->author,
            'isbn' => $book->isbn,
        ]);
    }

    public function test_他の書籍と重複するISBNでは更新できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 更新対象の書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'isbn' => '1111111111111',
        ]);

        // 他の書籍を作成（重複するISBNを設定）
        $otherBook = Book::factory()->create([
            'isbn' => '2222222222222',
        ]);

        // アクセストークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        // 更新する書籍情報（他の書籍と同じISBNを指定）
        $data = [
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => $otherBook->isbn,
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ];

        // 書籍更新APIにアクセス
        $response = $this->withToken($token)
            ->putJson("/api/v1/books/{$book->id}", $data);

        // バリデーションエラーを確認
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['isbn']);

        // 更新対象の書籍情報が変更されていないことを確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'isbn' => '1111111111111',
        ]);

        // 他の書籍のISBNが変更されていないことを確認
        $this->assertDatabaseHas('books', [
            'id' => $otherBook->id,
            'isbn' => '2222222222222',
        ]);
    }

    public function test_出版日が日付形式でない場合は更新できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // アクセストークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        // 更新する書籍情報（出版日を不正な形式にする）
        $data = [
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => '2222222222222',
            'published_date' => 'invalid-date',
            'genres' => [$genre->id],
        ];

        // 書籍更新APIにアクセス
        $response = $this->withToken($token)
            ->putJson("/api/v1/books/{$book->id}", $data);

        // バリデーションエラーを確認
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['published_date']);

        // 書籍情報が変更されていないことを確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => $book->title,
            'author' => $book->author,
            'isbn' => $book->isbn,
        ]);
    }

    public function test_ジャンルが未指定の場合は更新できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // アクセストークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        // 更新する書籍情報（ジャンルを未指定にする）
        $data = [
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => '2222222222222',
            'published_date' => '2026-01-01',
        ];

        // 書籍更新APIにアクセス
        $response = $this->withToken($token)
            ->putJson("/api/v1/books/{$book->id}", $data);

        // バリデーションエラーを確認
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['genres']);

        // 書籍情報が変更されていないことを確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => $book->title,
            'author' => $book->author,
            'isbn' => $book->isbn,
        ]);
    }

    public function test_ジャンルが空の場合は更新できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // アクセストークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        // 更新する書籍情報（ジャンルを空にする）
        $data = [
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => '2222222222222',
            'published_date' => '2026-01-01',
            'genres' => [],
        ];

        // 書籍更新APIにアクセス
        $response = $this->withToken($token)
            ->putJson("/api/v1/books/{$book->id}", $data);

        // バリデーションエラーを確認
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['genres']);

        // 書籍情報が変更されていないことを確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => $book->title,
            'author' => $book->author,
            'isbn' => $book->isbn,
        ]);
    }

    public function test_存在しないジャンルIDでは更新できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // アクセストークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        // 更新する書籍情報（存在しないジャンルIDを指定）
        $data = [
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => '2222222222222',
            'published_date' => '2026-01-01',
            'genres' => [999999],
        ];

        // 書籍更新APIにアクセス
        $response = $this->withToken($token)
            ->putJson("/api/v1/books/{$book->id}", $data);

        // バリデーションエラーを確認
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['genres.0']);

        // 書籍情報が変更されていないことを確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => $book->title,
            'author' => $book->author,
            'isbn' => $book->isbn,
        ]);
    }
}
