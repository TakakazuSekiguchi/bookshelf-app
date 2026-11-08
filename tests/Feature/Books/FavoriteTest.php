<?php

namespace Tests\Feature\Books;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\User;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_お気に入り一覧画面にお気に入りした書籍が表示される(): void
    {
        // ユーザーと書籍を作成
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // お気に入り登録
        $user->favoriteBooks()->attach($book->id);

        // お気に入り一覧画面を表示
        $response = $this->actingAs($user)
            ->get(route('favorites.index'));

        // お気に入りした書籍が表示されること
        $response->assertOk()
            ->assertSee($book->title);
    }

    public function test_お気に入り一覧は1ページに10件表示される(): void
    {
        // ユーザーと書籍を作成
        $user = User::factory()->create();
        $books = Book::factory()->count(11)->create();

        // お気に入り登録
        $user->favoriteBooks()->attach($books->modelKeys());

        // お気に入り一覧画面を表示
        $response = $this->actingAs($user)
            ->get(route('favorites.index'));

        // 1ページに10件表示されること
        $response->assertOk();

        $response->assertViewHas('books', function ($books) {
            return $books->count() === 10
                && $books->total() === 11;
        });
    }

    public function test_2ページ目に残りのお気に入り書籍が表示される()
    {
        // ユーザーと書籍を作成
        $user = User::factory()->create();
        $books = Book::factory()->count(11)->create();

        // お気に入り登録
        $user->favoriteBooks()->attach($books->modelKeys());

        // お気に入り一覧画面を表示
        $response = $this->actingAs($user)
            ->get(route('favorites.index', ['page' => 2]));

        // 2ページ目に残りのお気に入り書籍が表示
        $response->assertOk();

        $response->assertViewHas('books', function ($books) {
            return $books->count() === 1
                && $books->total() === 11;
        });
    }

    public function test_お気に入りを登録できる(): void
    {
        // ユーザーと書籍を作成
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // お気に入り登録
        $response = $this->actingAs($user)
            ->post(route('favorites.toggle', $book));

        // 登録結果を確認
        $this->assertDatabaseHas('book_favorite', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // リダイレクトを確認
        $response->assertRedirect();
    }

    public function test_お気に入りを解除できる(): void
    {
        // ユーザーと書籍を作成
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // お気に入り登録
        $user->favoriteBooks()->attach($book->id);

        // 登録されていることを確認
        $this->assertDatabaseHas('book_favorite', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // お気に入り解除
        $response = $this->actingAs($user)
            ->post(route('favorites.toggle', $book));

        // 削除されていることを確認
        $this->assertDatabaseMissing('book_favorite', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // リダイレクトを確認
        $response->assertRedirect();
    }

    public function test_未ログインの場合、お気に入り登録できない(): void
    {
        // 書籍を作成
        $book = Book::factory()->create();

        // 未ログイン状態でお気に入り登録
        $response = $this->post(route('favorites.toggle', $book));

        // ログイン画面へリダイレクトされること
        $response->assertRedirect(route('login'));

        // お気に入り登録されていないこと
        $this->assertDatabaseMissing('book_favorite', [
            'book_id' => $book->id,
        ]);
    }
}
