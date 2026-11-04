<?php

namespace Tests\Feature\Books;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;

class BookStoreTest extends TestCase
{
    use RefreshDatabase;

    // 正常系
    public function test_書籍が正しく登録できる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ログインユーザーとして書籍登録画面にアクセス
        $response = $this->actingAs($user)->get(route('books.create'));
        $response->assertOk();

        // ジャンル（複数指定）
        $genres = Genre::factory()->count(2)->create();

        // リクエストデータ
        $data = [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567891234',
            'published_date' => '1999-07-01',
            'description' => '書籍詳細テスト',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        // 登録リクエスト
        $response = $this->post(route('books.store'), $data);

        // リダイレクト確認
        $response->assertRedirect(route('books.index'));

        // booksテーブルに保存されているか
        $this->assertDatabaseHas('books', [
            'user_id'    => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567891234',
            'published_date' => '1999-07-01',
            'description' => '書籍詳細テスト',
            'image_url' => null,
        ]);

        // 保存された書籍を取得
        $book = Book::where('user_id', $user->id)->firstOrFail();

        // 中間テーブル（book_genre）に保存されているか
        foreach ($genres as $genre) {
            $this->assertDatabaseHas('book_genre', [
                'book_id'     => $book->id,
                'genre_id' => $genre->id,
            ]);
        }
    }

    // 異常系
    public function test_titleが未入力の場合、登録されない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genres = Genre::factory()->count(2)->create();

        // リクエストデータ
        $data = [
            'title' => '',
            'author' => 'テスト著者',
            'isbn' => '1234567891234',
            'published_date' => '1999-07-01',
            'description' => '書籍詳細テスト',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        // 登録リクエスト
        $response = $this->actingAs($user)->post(route('books.store'), $data);

        // バリデーションエラー確認
        $response->assertSessionHasErrors('title');

        // booksテーブルに登録されていないこと
        $this->assertDatabaseMissing('books', [
            'user_id' => $user->id,
            'author' => 'テスト著者',
            'isbn' => '1234567891234',
        ]);
    }

    public function test_authorが未入力の場合、登録されない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genres = Genre::factory()->count(2)->create();

        // リクエストデータ
        $data = [
            'title' => 'テスト書籍',
            'author' => '',
            'isbn' => '1234567891234',
            'published_date' => '1999-07-01',
            'description' => '書籍詳細テスト',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        // 登録リクエスト
        $response = $this->actingAs($user)->post(route('books.store'), $data);

        // バリデーションエラー確認
        $response->assertSessionHasErrors('author');

        // booksテーブルに登録されていないこと
        $this->assertDatabaseMissing('books', [
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'isbn' => '1234567891234',
        ]);
    }

    public function test_isbnが未入力の場合、登録されない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genres = Genre::factory()->count(2)->create();

        // リクエストデータ
        $data = [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '',
            'published_date' => '1999-07-01',
            'description' => '書籍詳細テスト',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        // 登録リクエスト
        $response = $this->actingAs($user)->post(route('books.store'), $data);

        // バリデーションエラー確認
        $response->assertSessionHasErrors('isbn');

        // booksテーブルに登録されていないこと
        $this->assertDatabaseMissing('books', [
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);
    }

    public function test_genresが未入力の場合、登録されない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genres = Genre::factory()->count(2)->create();

        // リクエストデータ
        $data = [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567891234',
            'published_date' => '1999-07-01',
            'description' => '書籍詳細テスト',
            'genres' => [],
        ];

        // 登録リクエスト
        $response = $this->actingAs($user)->post(route('books.store'), $data);

        // バリデーションエラー確認
        $response->assertSessionHasErrors('genres');

        // booksテーブルに登録されていないこと
        $this->assertDatabaseMissing('books', [
            'user_id' => $user->id,
            'title' => 'テスト書籍',
        ]);

        // book_genreテーブルに登録されていないこと
        foreach ($genres as $genre) {
            $this->assertDatabaseMissing('book_genre', [
                'genre_id' => $genre->id,
            ]);
        }
    }

    public function test_ISBNが重複している場合、登録されない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 既存の書籍を作成
        Book::factory()->create([
            'user_id' => $user->id,
            'isbn' => '1234567891234',
        ]);

        // ジャンルを作成
        $genres = Genre::factory()->count(2)->create();

        // リクエストデータ
        $data = [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567891234',
            'published_date' => '1999-07-01',
            'description' => '書籍詳細テスト',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        // 登録リクエスト
        $response = $this->actingAs($user)->post(route('books.store'), $data);

        // バリデーションエラー確認
        $response->assertSessionHasErrors('isbn');

        // booksテーブルに新しい書籍が登録されていないこと
        $this->assertDatabaseMissing('books', [
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);
    }

    public function test_未ログインの場合、登録できない(): void
    {
        // ジャンルを作成
        $genres = Genre::factory()->count(2)->create();

        // リクエストデータ
        $data = [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567891234',
            'published_date' => '1999-07-01',
            'description' => '書籍詳細テスト',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        // 未ログイン状態で登録リクエスト
        $response = $this->post(route('books.store'), $data);

        // ログイン画面にリダイレクトされること
        $response->assertRedirect(route('login'));

        // booksテーブルに登録されていないこと
        $this->assertDatabaseMissing('books', [
            'isbn' => '1234567891234',
        ]);
    }

    public function test_存在しないジャンルIDを指定した場合、登録されない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // リクエストデータ
        $data = [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567891234',
            'published_date' => '1999-07-01',
            'description' => '書籍詳細テスト',
            'genres' => [9999],
        ];

        // 登録リクエスト
        $response = $this->actingAs($user)->post(route('books.store'), $data);

        // バリデーションエラー確認
        $response->assertSessionHasErrors('genres.0');

        // booksテーブルに登録されていないこと
        $this->assertDatabaseMissing('books', [
            'user_id' => $user->id,
            'isbn' => '1234567891234',
        ]);

        // book_genreテーブルに登録されていないこと
        $this->assertDatabaseCount('book_genre', 0);
    }
}

