<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\User;

class BookDestroyTest extends TestCase
{
    use RefreshDatabase;

    public function test_書籍を削除できる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // アクセストークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        // APIで書籍を削除
        $response = $this->withToken($token)
            ->deleteJson("/api/v1/books/{$book->id}");

        // 204（コンテンツなし）の確認
        $response->assertNoContent();

        // データベースから書籍が削除されていることを確認
        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    // 認証・認可
    public function test_未認証ユーザーは書籍を削除できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // アクセストークンを発行しない状態で削除リクエスト
        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        // 401（認証エラー）の確認
        $response->assertUnauthorized();

        // 書籍が削除されていないことを確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }

    public function test_他のユーザーが登録した書籍は削除できない(): void
    {
        // ユーザーA（書籍の所有者）を作成
        $userA = User::factory()->create();

        // ユーザーB（削除を試みるユーザー）を作成
        $userB = User::factory()->create();

        // ユーザーAの書籍を作成
        $book = Book::factory()->create([
            'user_id' => $userA->id,
        ]);

        // ユーザーBのアクセストークンを発行
        $token = $userB->createToken('test-token')->plainTextToken;

        // ユーザーBが書籍を削除
        $response = $this->withToken($token)
            ->deleteJson("/api/v1/books/{$book->id}");

        // 403（アクセス拒否）の確認
        $response->assertForbidden();

        // 書籍が削除されていないことを確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }

    public function test_存在しない書籍は削除できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // アクセストークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        // 存在しない書籍IDを指定して削除リクエスト
        $response = $this->withToken($token)
            ->deleteJson('/api/v1/books/99999');

        // 404（Not Found）の確認
        $response->assertNotFound();
    }
}
