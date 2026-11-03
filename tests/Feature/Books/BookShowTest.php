<?php

namespace Tests\Feature\Books;

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

    public function test_ゲストが書籍詳細画面にアクセスできる(): void
    {
        // 書籍を作成
        $book = Book::factory()->create();

        // 書籍詳細画面にアクセス
        $response = $this->get(route('books.show', $book));

        // HTTPステータス200を確認
        $response->assertOk();
    }

    public function test_ログインユーザーが書籍詳細画面にアクセスできる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create();

        // ログインユーザーとして書籍詳細画面にアクセス
        $response = $this->actingAs($user)->get(route('books.show', $book));

        // HTTPステータス200を確認
        $response->assertOk();
    }

    public function test_書籍の情報が正しく表示される(): void
    {
        // 書籍を作成
        $book = Book::factory()->create([
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567891234',
            'published_date' => '1999-07-01',
            'description' => '書籍詳細テスト',
        ]);

        // 書籍詳細画面にアクセス
        $response = $this->get(route('books.show', $book));

        // 書籍情報が表示されることを確認
        $response->assertOk();
        $response->assertSee('テスト書籍');
        $response->assertSee('テスト著者');
        $response->assertSee('1234567891234');
        $response->assertSee('1999-07-01');
        $response->assertSee('書籍詳細テスト');
    }

    public function test_複数選択されたジャンルが表示されている(): void
    {
        // 書籍を作成
        $book = Book::factory()->create();

        $genres = Genre::factory()->createMany([
            ['name' => '小説'],
            ['name' => 'ビジネス'],
            ['name' => '技術書'],
        ]);

        // 書籍にジャンルを紐づけ
        $book->genres()->attach($genres->pluck('id'));

        // 書籍詳細画面にアクセス
        $response = $this->get(route('books.show', $book));

        // 複数ジャンルが表示されていることを確認
        $response->assertOk();
        $response->assertSee('小説');
        $response->assertSee('ビジネス');
        $response->assertSee('技術書');
    }

    public function test_書籍に紐づくレビューが表示される(): void
    {
        // レビューにコメントをするユーザーを作成
        $user = User::factory()->create([
            'name' => 'レビューユーザー',
        ]);

        // 書籍を作成
        $book = Book::factory()->create();

        // レビュー
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '感動的なストーリーでした',
        ]);

        // 書籍詳細画面にアクセス
        $response = $this->get(route('books.show', $book));

        // レビュー情報が表示されることを確認
        $response->assertOk();
        $response->assertSee('レビューユーザー');
        $response->assertSee('感動的なストーリーでした');
    }

    public function test_ログインユーザーにのみレビュー投稿フォームが表示される(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create();

        // ログインユーザーとして書籍詳細画面にアクセス
        $response = $this->actingAs($user)->get(route('books.show', $book));

        // レビュー投稿フォームが表示されることを確認
        $response->assertOk();
        $response->assertSee('レビューを投稿');
        $response->assertSee('評価');
        $response->assertSee('コメント');
    }

    public function test_ゲストにレビュー投稿フォームが表示されない(): void
    {
        // 書籍を作成
        $book = Book::factory()->create();

        // ゲストとして書籍詳細画面にアクセス
        $response = $this->get(route('books.show', $book));

        // レビュー投稿フォームが表示されないことを確認
        $response->assertOk();
        $response->assertSee('レビューを投稿するには');
    }
}
