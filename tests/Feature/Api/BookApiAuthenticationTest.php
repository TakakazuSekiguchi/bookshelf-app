<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;

class BookApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_有効なトークンで書籍登録APIにアクセスできる(): void
    {
        // テスト用ユーザーを作成
        $user = User::factory()->create();

        // テスト用ジャンルを作成
        $genre = Genre::factory()->create();

        // Sanctumのトークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        // 書籍登録APIにアクセス
        $response = $this->withToken($token)
            ->postJson('/api/v1/books', [
                'title' => 'テスト書籍',
                'author' => 'テスト著者',
                'isbn' => '1234567891234',
                'published_date' => '1999-07-01',
                'description' => 'テスト用の書籍です。',
                'image_url' => 'https://example.com/book.jpg',
                'genres' => [$genre->id],
            ]);

        // 書籍が正常に登録されることを確認
        $response->assertCreated();

        // データベースに書籍が登録されていることを確認
        $this->assertDatabaseHas('books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567891234',
            'user_id' => $user->id,
        ]);
    }

    // 異常系
    public function test_トークンなしでは書籍登録APIにアクセスできない(): void
    {
        // 書籍登録APIにアクセス（トークンなし）
        $response = $this->postJson('/api/v1/books');

        // 認証エラー（401）が返されることを確認
        $response->assertUnauthorized();
    }

    public function test_無効なトークンでは書籍登録APIにアクセスできない(): void
    {
        // 無効なトークンを付与して書籍登録APIにアクセス
        $response = $this->withToken('invalid-token')
            ->postJson('/api/v1/books');

        // 認証エラー（401）が返されることを確認
        $response->assertUnauthorized();
    }

    // 認証不要
    public function test_認証不要の書籍一覧APIにはトークンなしでアクセスできる(): void
    {
        // テスト用書籍を作成
        $book = Book::factory()->create();

        // トークンを付与せず書籍一覧APIにアクセス
        $response = $this->getJson('/api/v1/books');

        // 正常にアクセスできることを確認
        $response->assertOk();

        // 作成した書籍がレスポンスに含まれていることを確認
        $response->assertJsonFragment([
            'title' => $book->title,
        ]);
    }
}
