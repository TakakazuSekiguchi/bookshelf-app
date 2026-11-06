<?php

namespace Tests\Feature\Books;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\Book;

class BookIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_登録された書籍が一覧に表示される()
    {        
        $book = Book::factory()->create([
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);

        $response = $this->get(route('books.index'));

        $response->assertOk();

        $response->assertSee('テスト書籍');
        $response->assertSee('テスト著者');
    }

    public function test_書籍一覧は1ページに10件表示される()
    {
        Book::factory()->count(11)->create();

        $response = $this->get(route('books.index'));

        $response->assertOk();

        $response->assertViewHas('books', function ($books) {
            return $books->count() === 10
                && $books->total() === 11;
        });
    }

    public function test_2ページ目に残りの書籍が表示される()
    {
        Book::factory()->count(11)->create();

        $response = $this->get(route('books.index', ['page' => 2]));

        $response->assertOk();

        $response->assertViewHas('books', function ($books) {
            return $books->count() === 1
                && $books->total() === 11;
        });
    }
}
