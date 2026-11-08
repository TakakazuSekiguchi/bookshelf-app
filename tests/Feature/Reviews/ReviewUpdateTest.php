<?php

namespace Tests\Feature\Reviews;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Review;
use App\Models\User;

class ReviewUpdateTest extends TestCase
{
    use RefreshDatabase;

    // 正常系
    public function test_レビューを正常に編集できる(): void
    {
        // ユーザーと書籍を作成
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // レビューを作成
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '更新前のレビュー',
        ]);

        // リクエストデータ
        $updateData = [
            'rating' => 5,
            'comment' => '更新後のレビュー',
        ];

        // レビュー編集
        $response = $this->actingAs($user)
            ->put(route('reviews.update', $review), $updateData);

        // リダイレクト確認
        $response->assertRedirect(route('books.show', $book));

        // reviewsテーブルに保存されているか
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '更新後のレビュー',
        ]);
    }

    // 認可
    public function test_他のユーザーのレビューは編集できない(): void
    {
        // 二名分のユーザーをそれぞれ作成
        $user = User::factory()->create(); // ログインするユーザー
        $otherUser = User::factory()->create(); // 他ユーザー

        // 書籍を作成
        $book = Book::factory()->create();

        // 他のユーザーのレビューを作成
        $review = Review::factory()->create([
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => 'ユーザーBのレビュー',
        ]);

        // リクエストデータ
        $updateData = [
            'rating' => 5,
            'comment' => '更新後のレビュー',
        ];

        // レビュー編集
        $response = $this->actingAs($user)
            ->put(route('reviews.update', $review), $updateData);

        // 403 Forbidden が返されることを確認
        $response->assertForbidden();

        // レビュー情報が更新されていないことを確認
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => 'ユーザーBのレビュー',
        ]);
    }

    // 異常系
    public function test_未ログインの場合、レビュー編集できない(): void
    {
        // ユーザーと書籍を作成
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // レビューを作成
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '更新前のレビュー',
        ]);

        // リクエストデータ
        $updateData = [
            'rating' => 5,
            'comment' => '更新後のレビュー',
        ];

        // レビュー編集
        $response = $this->put(route('reviews.update', $review), $updateData);

        // ログイン画面にリダイレクトされること
        $response->assertRedirect(route('login'));

        // reviewsテーブルが更新されていないこと
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 3,
            'comment' => '更新前のレビュー',
        ]);
    }

    public function test_評価が未入力の場合、バリデーションエラーになる(): void
    {
        // ユーザーと書籍を作成
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // レビューを作成
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '更新前のレビュー',
        ]);

        // リクエストデータ
        $updateData = [
            'rating' => '',
            'comment' => '更新後のレビュー',
        ];

        // レビュー編集
        $response = $this->actingAs($user)
            ->put(route('reviews.update', $review), $updateData);

            // バリデーションエラー確認
        $response->assertSessionHasErrors('rating');

        // reviewsテーブルが更新されていないこと
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 3,
            'comment' => '更新前のレビュー',
        ]);
    }

    public function test_評価が0（評価が1〜5の範囲外）の場合、バリデーションエラーになる(): void
    {
        // ユーザーと書籍を作成
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // レビューを作成
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '更新前のレビュー',
        ]);

        // リクエストデータ
        $updateData = [
            'rating' => 0,
            'comment' => '更新後のレビュー',
        ];

        // レビュー編集
        $response = $this->actingAs($user)
            ->put(route('reviews.update', $review), $updateData);

        // バリデーションエラー確認
        $response->assertSessionHasErrors('rating');

        // reviewsテーブルが更新されていないこと
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 3,
            'comment' => '更新前のレビュー',
        ]);
    }

    public function test_評価が6（評価が1〜5の範囲外）の場合、バリデーションエラーになる(): void
    {
        // ユーザーと書籍を作成
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // レビューを作成
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '更新前のレビュー',
        ]);

        // リクエストデータ
        $updateData = [
            'rating' => 6,
            'comment' => '更新後のレビュー',
        ];

        // レビュー編集
        $response = $this->actingAs($user)
            ->put(route('reviews.update', $review), $updateData);

        // バリデーションエラー確認
        $response->assertSessionHasErrors('rating');

        // reviewsテーブルが更新されていないこと
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 3,
            'comment' => '更新前のレビュー',
        ]);
    }

    public function test_コメントが未入力の場合、バリデーションエラーになる(): void
    {
        // ユーザーと書籍を作成
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // レビューを作成
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '更新前のレビュー',
        ]);

        // リクエストデータ
        $updateData = [
            'rating' => 3,
            'comment' => '',
        ];

        // レビュー編集
        $response = $this->actingAs($user)
            ->put(route('reviews.update', $review), $updateData);

        // バリデーションエラー確認
        $response->assertSessionHasErrors('comment');

        // reviewsテーブルが更新されていないこと
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 3,
            'comment' => '更新前のレビュー',
        ]);
    }
}
