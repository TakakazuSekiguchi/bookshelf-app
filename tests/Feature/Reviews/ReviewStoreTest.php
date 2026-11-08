<?php

namespace Tests\Feature\Reviews;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Review;
use App\Models\User;

class ReviewStoreTest extends TestCase
{
    use RefreshDatabase;

    // 正常系
    public function test_レビューを正常に投稿できる(): void
    {
        // ユーザーと書籍を作成
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // リクエストデータ
        $review = [
            'rating' => 3,
            'comment' => 'レビューコメントテスト',
        ];

        // レビュー登録
        $response = $this->actingAs($user)
            ->post(route('reviews.store', $book), $review);

        // リダイレクト確認
        $response->assertRedirect(route('books.show', $book));

        // reviewsテーブルに保存されているか
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => 'レビューコメントテスト',
        ]);
    }

    public function test_投稿したレビューが書籍詳細に表示される(): void
    {
        // ユーザーと書籍を作成
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // レビューを作成
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => 'レビュー表示テスト',
        ]);

        // 書籍詳細画面を取得
        $response = $this->actingAs($user)
            ->get(route('books.show', $book));

        // 書籍詳細画面の表示確認
        $response->assertOk();

        // 投稿したレビューの内容が表示されることを確認
        $response->assertSee('レビュー表示テスト');
    }

    // 異常系
    public function test_未ログインの場合、レビュー登録できない(): void
    {
        // ユーザーと書籍を作成
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // リクエストデータ
        $review = [
            'rating' => 3,
            'comment' => 'レビューコメントテスト',
        ];

        // レビュー登録
        $response = $this->post(route('reviews.store', $book), $review);

        // ログイン画面にリダイレクトされること
        $response->assertRedirect(route('login'));

        // reviewsテーブルに登録されていないこと
        $this->assertDatabaseMissing('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => 'レビューコメントテスト',
        ]);
    }

    public function test_評価が未入力の場合、バリデーションエラーになる(): void
    {
        // ユーザーと書籍を作成
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // リクエストデータ
        $review = [
            'rating' => '',
            'comment' => 'レビューコメントテスト',
        ];

        // レビュー登録
        $response = $this->actingAs($user)
            ->post(route('reviews.store', $book), $review);

        // バリデーションエラー確認
        $response->assertSessionHasErrors('rating');

        // reviewsテーブルに登録されていないこと
        $this->assertDatabaseMissing('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_評価が0（評価が1〜5の範囲外）の場合、バリデーションエラーになる(): void
    {
        // ユーザーと書籍を作成
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // 範囲外の評価を指定
        $review = [
            'rating' => 0,
            'comment' => 'レビューコメントテスト',
        ];

        // レビュー登録
        $response = $this->actingAs($user)
            ->post(route('reviews.store', $book), $review);

        // バリデーションエラー確認
        $response->assertSessionHasErrors('rating');

        // reviewsテーブルに登録されていないこと
        $this->assertDatabaseMissing('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_評価が6（評価が1〜5の範囲外）の場合、バリデーションエラーになる(): void
    {
        // ユーザーと書籍を作成
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // 範囲外の評価を指定
        $review = [
            'rating' => 6,
            'comment' => 'レビューコメントテスト',
        ];

        // レビュー登録
        $response = $this->actingAs($user)
            ->post(route('reviews.store', $book), $review);

        // バリデーションエラー確認
        $response->assertSessionHasErrors('rating');

        // reviewsテーブルに登録されていないこと
        $this->assertDatabaseMissing('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_コメントが未入力の場合、バリデーションエラーになる(): void
    {
        // ユーザーと書籍を作成
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // リクエストデータ
        $review = [
            'rating' => 3,
            'comment' => '',
        ];

        // レビュー登録
        $response = $this->actingAs($user)
            ->post(route('reviews.store', $book), $review);

        // バリデーションエラー確認
        $response->assertSessionHasErrors('comment');

        // reviewsテーブルに登録されていないこと
        $this->assertDatabaseMissing('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }
}
