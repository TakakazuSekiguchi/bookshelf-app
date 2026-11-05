<?php

namespace Tests\Feature\Books;

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
    public function test_書籍情報を正常に更新できる(): void
    {
        // ユーザーと書籍を作成
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'isbn' => '1234567891234',
        ]);

        $genres = Genre::factory()->count(2)->create();

        // 更新する書籍情報
        $updateData = [
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => '1234567891234',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        // ログインして書籍を更新
        $response = $this->actingAs($user)
            ->put(route('books.update', $book), $updateData);

        // 更新後のリダイレクトを確認
        $response->assertRedirect();

        // DBの書籍情報が更新されていることを確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => '1234567891234',
        ]);
    }

    public function test_更新したジャンルが正しく紐付けられる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 更新前のジャンルを作成
        $oldGenre = Genre::factory()->create();

        // 更新後のジャンルを作成
        $newGenre = Genre::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 更新前のジャンルを紐付け
        $book->genres()->attach($oldGenre->id);

        // 書籍情報を更新
        $response = $this->actingAs($user)->put(
            route('books.update', $book),
            [
                'title' => '更新後のタイトル',
                'author' => '更新後の著者',
                'isbn' => '1234567891234',
                'genres' => [$newGenre->id],
            ]
        );

        // 更新処理の成功を確認
        $response->assertRedirect();

        // 更新後のジャンルが紐付いていることを確認
        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $newGenre->id,
        ]);

        // 更新前のジャンルが解除されていることを確認
        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $oldGenre->id,
        ]);
    }

    // 異常系
    public function test_タイトルが未入力の場合、更新されない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '更新前のタイトル',
        ]);

        // タイトルを空にして更新
        $response = $this->actingAs($user)->put(
            route('books.update', $book),
            [
                'title' => '',
                'author' => '更新後の著者',
                'isbn' => $book->isbn,
                'genres' => [],
            ]
        );

        // バリデーションエラーを確認
        $response->assertSessionHasErrors('title');

        // タイトルが更新されていないことを確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新前のタイトル',
        ]);
    }

    public function test_著者が未入力の場合、更新されない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'author' => '更新前の著者',
        ]);

        // タイトルを空にして更新
        $response = $this->actingAs($user)->put(
            route('books.update', $book),
            [
                'title' => '更新後のタイトル',
                'author' => '',
                'isbn' => $book->isbn,
                'genres' => [],
            ]
        );

        // バリデーションエラーを確認
        $response->assertSessionHasErrors('author');

        // タイトルが更新されていないことを確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'author' => '更新前の著者',
        ]);
    }

    public function test_ISBNが不正な形式の場合、更新されない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'isbn' => '1234567891234',
        ]);

        // 不正なISBNで更新
        $response = $this->actingAs($user)->put(
            route('books.update', $book),
            [
                'title' => '更新後のタイトル',
                'author' => '更新後の著者',
                'isbn' => '123456789',
                'genres' => [],
            ]
        );

        // ISBNのバリデーションエラーを確認
        $response->assertSessionHasErrors('isbn');

        // ISBNが更新されていないことを確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'isbn' => '1234567891234',
        ]);
    }

    public function test_存在しないジャンルIDを指定した場合、更新されない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 更新前のジャンルを紐付け
        $book->genres()->attach($genre->id);

        // 存在しないジャンルIDで更新
        $response = $this->actingAs($user)->put(
            route('books.update', $book),
            [
                'title' => '更新後のタイトル',
                'author' => '更新後の著者',
                'isbn' => $book->isbn,
                'genres' => [99999],
            ]
        );

        // ジャンルのバリデーションエラーを確認
        $response->assertSessionHasErrors('genres.0');

        // 書籍のタイトルが更新されていないことを確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => $book->title,
        ]);

        // 更新前のジャンルとの紐付けが維持されていることを確認
        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    // 認証・認可
    public function test_未ログインの場合、書籍を更新できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '更新前のタイトル',
        ]);

        // 未ログイン状態で更新リクエスト
        $response = $this->put(
            route('books.update', $book),
            [
                'title' => '更新後のタイトル',
                'author' => '更新後の著者',
                'isbn' => $book->isbn,
                'genres' => [],
            ]
        );

        // ログイン画面にリダイレクトされることを確認
        $response->assertRedirect(route('login'));

        // 書籍が更新されていないことを確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新前のタイトル',
        ]);
    }

    public function test_他ユーザーの書籍を更新できない(): void
    {
        // 二名分のユーザーをそれぞれ作成
        $user = User::factory()->create(); // ログインするユーザー
        $otherUser = User::factory()->create(); // 他ユーザー

        // 他ユーザーの書籍を作成
        $book = Book::factory()->create([
            'user_id' => $otherUser->id,
            'title' => '更新前のタイトル',
        ]);

        $genre = Genre::factory()->create();

        // 別ユーザーとして更新リクエスト
        $response = $this->actingAs($user)->put(
            route('books.update', $book),
            [
                'title' => '更新後のタイトル',
                'author' => '更新後の著者',
                'isbn' => $book->isbn,
                // 'genres' => [],
                'genres' => [$genre->id],
            ]
        );

        // 403 Forbidden が返されることを確認
        $response->assertForbidden();

        // 書籍情報が更新されていないことを確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新前のタイトル',
            'user_id' => $otherUser->id,
        ]);
    }

    public function test_存在しない書籍IDを指定した場合、更新できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 存在しない書籍IDを指定して更新リクエスト
        $response = $this->actingAs($user)->put(
            route('books.update', 99999),
            [
                'title' => '更新後のタイトル',
                'author' => '更新後の著者',
                'isbn' => '1234567891234',
                'genres' => [],
            ]
        );

        // 404 Not Found が返されることを確認
        $response->assertNotFound();
    }
}
