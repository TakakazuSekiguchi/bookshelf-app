<?php

namespace Tests\Feature\Reviews;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Review;
use App\Models\User;

class ReviewDestroyTest extends TestCase
{
    use RefreshDatabase;

    // 正常系
    public function test_レビューを正常に削除できる(): void
    {
        // ユーザーと書籍を作成
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // レビューを作成
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '削除前のレビュー',
        ]);

        // ログインユーザーとしてログイン
        $this->actingAs($user);

        // 削除リクエスト
        $response = $this->delete(route('reviews.destroy', $review));

        // リダイレクト確認
        $response->assertRedirect(route('books.show', $book));

        // reviewsテーブルにデータが保存されていないことを確認
        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
            'comment' => '削除前のレビュー',
        ]);
    }

    // 認可
    public function test_他のユーザーのレビューは削除できない(): void
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
            'comment' => '削除前のレビュー',
        ]);

        // ログインユーザーとしてログイン
        $this->actingAs($user);

        // 削除リクエスト
        $response = $this->delete(route('reviews.destroy', $review));

        // 403 Forbidden が返されることを確認
        $response->assertForbidden();

        // レビュー情報が削除されていないことを確認
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'comment' => '削除前のレビュー',
        ]);
    }

    // 異常系
    public function test_未ログインの場合、レビュー削除できない(): void
    {
        // ユーザーと書籍を作成
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // レビューを作成
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '削除前のレビュー',
        ]);

        // 削除リクエスト
        $response = $this->delete(route('reviews.destroy', $review));

        // ログイン画面にリダイレクトされること
        $response->assertRedirect(route('login'));

        // レビュー情報が削除されていないことを確認
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'comment' => '削除前のレビュー',
        ]);
    }
}
