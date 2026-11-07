<?php

namespace Tests\Feature\Books;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Review;

class BookSortTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_新しい順で書籍が表示される(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 出版日の異なる3つの書籍を作成
        $oldBook = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '2020年出版の書籍',
            'published_date' => '2020-01-01',
        ]);

        $middleBook = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '2022年出版の書籍',
            'published_date' => '2022-01-01',
        ]);

        $newBook = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '2024年出版の書籍',
            'published_date' => '2024-01-01',
        ]);

        // 新しい順で書籍一覧を取得
        $response = $this->actingAs($user)
            ->get(route('books.index', ['sort' => 'newest']));

        // 正常に表示されること
        $response->assertOk();

        // 公開日の降順で表示されること
        $books = $response->viewData('books');

        $this->assertEquals([
                $newBook->id,
                $middleBook->id,
                $oldBook->id,
            ],
            $books->pluck('id')->all()
        );
    }

    public function test_古い順で書籍が表示される(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 出版日の異なる3つの書籍を作成
        $oldBook = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '2020年出版の書籍',
            'published_date' => '2020-01-01',
        ]);

        $middleBook = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '2022年出版の書籍',
            'published_date' => '2022-01-01',
        ]);

        $newBook = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '2024年出版の書籍',
            'published_date' => '2024-01-01',
        ]);

        // 古い順で書籍一覧を取得
        $response = $this->actingAs($user)
            ->get(route('books.index', ['sort' => 'oldest']));

        // 正常に表示されること
        $response->assertOk();

        // 公開日の降順で表示されること
        $books = $response->viewData('books');

        $this->assertEquals([
                $oldBook->id,
                $middleBook->id,
                $newBook->id,
            ],
            $books->pluck('id')->all()
        );
    }

    public function test_評価順で書籍が表示される(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 3つの書籍を作成
        $bookA = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '書籍A',
        ]);

        $bookB = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '書籍B',
        ]);

        $bookC = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '書籍C',
        ]);

        // 複数件のレビューを作成
        // 「bookA：平均評価4.5　bookB：平均評価3.0　bookC：平均評価5.0」で設定

        // bookA
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $bookA->id,
            'rating' => 4,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $bookA->id,
            'rating' => 5,
        ]);

        // bookB
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $bookB->id,
            'rating' => 3,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $bookB->id,
            'rating' => 3,
        ]);

        // bookC
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $bookC->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $bookC->id,
            'rating' => 5,
        ]);

        // 評価順で書籍一覧を取得
        $response = $this->actingAs($user)
            ->get(route('books.index', ['sort' => 'rating']));

        // 正常に表示されること
        $response->assertOk();

        // 平均評価の降順で表示されること
        $books = $response->viewData('books');

        $this->assertEquals(
            [
                $bookC->id, // 平均評価 5.0
                $bookA->id, // 平均評価 4.5
                $bookB->id, // 平均評価 3.0
            ],
            $books->pluck('id')->all()
        );
    }

    public function test_タイトル順で書籍が表示される(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 順不同で4つの書籍を作成
        $bookC = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'Cタイトル',
        ]);

        $bookA = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'Aタイトル',
        ]);

        $bookD = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'Dタイトル',
        ]);

        $bookB = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'Bタイトル',
        ]);

        // タイトル順で書籍一覧を取得
        $response = $this->actingAs($user)
            ->get(route('books.index', ['sort' => 'title']));

        // 正常に表示されること
        $response->assertOk();

        // タイトルの昇順で表示されること
        $books = $response->viewData('books');

        $this->assertEquals(
            [
                $bookA->id,
                $bookB->id,
                $bookC->id,
                $bookD->id,
            ],
            $books->pluck('id')->all()
        );
    }
}
