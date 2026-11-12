<?php

namespace Tests\Feature\Reviews;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Review;
use App\Models\User;

class ReviewLikeTest extends TestCase
{
    use RefreshDatabase;

    // 正常系
    public function test_他ユーザーのレビューにいいねができる(): void
    {
        // ログインユーザーを作成（いいねをするユーザー）
        $user = User::factory()->create();

        // 別ユーザーを作成（レビューを書いた人、いいねをされるユーザー）
        $otherUser = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create();

        // 別ユーザーのレビューを作成
        $review = Review::factory()->create([
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
        ]);

        // いいね操作
        $response = $this->actingAs($user)
            ->post(route('reviews.like', $review));

        // レスポンス確認
        $response->assertRedirect();

        // review_likeテーブルにいいねが登録されていることを確認
        $this->assertDatabaseHas('review_like', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    public function test_いいね済みのレビューのいいねを解除できる(): void
    {
        // ログインユーザーを作成
        $user = User::factory()->create();

        // 別ユーザーを作成
        $otherUser = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create();

        // 別ユーザーのレビューを作成
        $review = Review::factory()->create([
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
        ]);

        // ログインユーザーがいいねする
        $this->actingAs($user)
            ->post(route('reviews.like', $review));

        // いいねが登録されていることを確認
        $this->assertDatabaseHas('review_like', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        // 同じレビューにもう一度いいねする
        $response = $this->post(route('reviews.like', $review));

        // レスポンス確認
        $response->assertRedirect();

        // いいねが解除されていることを確認
        $this->assertDatabaseMissing('review_like', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    // 異常系
    public function test_未ログインの場合、レビューにいいねができない(): void
    {
        // 別ユーザーを作成（レビューを書いた人、いいねをされるユーザー）
        $otherUser = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create();

        // 別ユーザーのレビューを作成
        $review = Review::factory()->create([
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
        ]);

        // 未ログイン状態でいいね操作を行う
        $response = $this->post(route('reviews.like', $review));

        // ログイン画面にリダイレクトされること
        $response->assertRedirect(route('login'));

        // review_likeテーブルにいいねが登録されていないことを確認
        $this->assertDatabaseMissing('review_like', [
            'review_id' => $review->id,
        ]);
    }
}