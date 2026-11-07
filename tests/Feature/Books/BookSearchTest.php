<?php

namespace Tests\Feature\Books;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;

class BookSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_タイトルで検索できる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 検索対象の書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
        ]);

        // 検索対象外の書籍を作成
        $otherBook = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '検索対象外書籍',
        ]);

        // タイトルで検索
        $response = $this->get(route('books.index', [
            'keyword' => 'テスト書籍',
        ]));

        // 検索結果の確認
        $response->assertOk();
        $response->assertSee($book->title);
        $response->assertDontSee($otherBook->title);
    }

    public function test_著者名で検索できる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 検索対象の著者を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'author' => 'テスト著者',
        ]);

        // 検索対象外の著者を作成
        $otherBook = Book::factory()->create([
            'user_id' => $user->id,
            'author' => '検索対象外著者',
        ]);

        // 著者で検索
        $response = $this->get(route('books.index', [
            'keyword' => 'テスト著者',
        ]));

        // 検索結果の確認
        $response->assertOk();
        $response->assertSee($book->author);
        $response->assertDontSee($otherBook->author);
    }

    public function test_部分一致で検索できる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 検索対象の書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト部分一致書籍',
        ]);

        // 検索対象外の書籍を作成
        $otherBook = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '検索対象外書籍',
        ]);

        // タイトルで検索
        $response = $this->get(route('books.index', [
            'keyword' => '部分一致',
        ]));

        // 検索結果の確認
        $response->assertOk();
        $response->assertSee($book->title);
        $response->assertDontSee($otherBook->title);
    }

    public function test_ジャンルを指定して検索できる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create([
            'name' => '技術書',
        ]);

        // 検索対象外のジャンル
        $otherGenre = Genre::factory()->create([
            'name' => '小説',
        ]);

        // 検索対象の書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
        ]);
        $book->genres()->attach($genre->id);

        // 検索対象外の書籍を作成
        $otherBook = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '検索対象外書籍',
        ]);
        $otherBook->genres()->attach($otherGenre->id);

        // ジャンルを指定して検索
        $response = $this->get(route('books.index', [
            'genre' => $genre->id,
        ]));

        // 検索結果の確認
        $response->assertOk();
        $response->assertSee($book->title);
        $response->assertDontSee($otherBook->title);
    }

    public function test_キーワードとジャンルを指定して検索できる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを作成
        $genre = Genre::factory()->create([
            'name' => '技術書',
        ]);

        // 検索対象外のジャンル
        $otherGenre = Genre::factory()->create([
            'name' => '小説',
        ]);

        // 検索対象の書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
        ]);
        $book->genres()->attach($genre->id);

        // 検索対象外の書籍を作成（検索条件とタイトルが異なる）
        $otherTitleBook = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '検索対象外書籍',
        ]);
        $otherTitleBook->genres()->attach($genre->id);

        // 検索対象外の書籍を作成（検索条件とジャンルが異なる）
        $otherGenreBook = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '別ジャンルの書籍',
        ]);
        $otherGenreBook->genres()->attach($otherGenre->id);

        // ジャンルとキーワードを指定して検索
        $response = $this->get(route('books.index', [
            'keyword' => 'テスト書籍',
            'genre' => $genre->id,
        ]));

        // 検索結果の確認
        $response->assertOk();
        $response->assertSee($book->title);
        $response->assertDontSee($otherTitleBook->title);
        $response->assertDontSee($otherGenreBook->title);
    }
}