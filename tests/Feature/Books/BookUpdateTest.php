<?php

namespace Tests\Feature\Books;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;

class BookUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_書籍情報を正常に更新できる(): void
    {
        // ユーザーと書籍を作成
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 更新する書籍情報
        $updateData = [
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => '9784873117584',
            'genres' => [1, 2],
        ];

        // ログインして書籍を更新
        $response = $this->actingAs($user)
            ->put(route('books.update', $book), $updateData);

        // 更新後のリダイレクトを確認
        $response->assertRedirect();

        // DBの書籍情報が更新されていることを確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => '9784873117584',
        ]);
    }
}
