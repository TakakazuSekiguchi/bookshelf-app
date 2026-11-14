<?php

namespace Tests\Feature\Books;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\Book;
use App\Models\User;

class BookDestroyTest extends TestCase
{
    use RefreshDatabase;

    // 正常系
    public function test_書籍を正常に削除できる(): void
    {
        // ユーザーと書籍を作成
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // ログインユーザーとしてログイン
        $this->actingAs($user);

        // 削除リクエスト
        $response = $this->delete(route('books.destroy', $book));

        // 書籍一覧画面へリダイレクトされること
        $response->assertRedirect(route('books.index'));

        // booksテーブルにデータが保存されていないことを確認
        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }
    
    // 認証・認可
    public function test_未ログインの場合、書籍を削除できない(): void
    {
        // ユーザーと書籍を作成
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '削除前のタイトル',
        ]);

        // 削除リクエスト
        $response = $this->delete(route('books.destroy', $book));

        // ログイン画面へリダイレクトされること
        $response->assertRedirect(route('login'));

        // booksテーブルにデータが残っていることを確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '削除前のタイトル',
        ]);
    }

    public function test_他のユーザーが登録した書籍を削除できない(): void
    {
        // 二名分のユーザーをそれぞれ作成
        $user = User::factory()->create(); // ログインするユーザー
        $otherUser = User::factory()->create(); // 他ユーザー

        // 他ユーザーの書籍を作成
        $book = Book::factory()->create([
            'user_id' => $otherUser->id,
            'title' => '削除前のタイトル',
        ]);

        // 別のユーザーとしてログイン
        $this->actingAs($user);

        // 削除リクエスト
        $response = $this->delete(route('books.destroy', $book));

        // 403 Forbidden が返されることを確認
        $response->assertForbidden();

        // 書籍情報が削除されていないことを確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '削除前のタイトル',
            'user_id' => $otherUser->id,
        ]);
    }

    public function test_存在しない書籍を削除できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 存在しない書籍IDを指定して削除リクエスト
        $response = $this->actingAs($user)->delete(
            route('books.destroy', 99999)
        );

        // 404 Not Found が返されることを確認
        $response->assertNotFound();
    }
}

