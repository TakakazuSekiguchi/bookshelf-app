<?php

namespace Tests\Feature\ReadingReport;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
// use App\Models\ReadingPlan;
use App\Models\User;
// use App\Enums\ReadingPlanStatus;

class ReadingReportTopRatedBooksTest extends TestCase
{
    use RefreshDatabase;

    // 高評価書籍TOP5
    public function test_星4以上の書籍が表示される(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を2冊作成
        $books = Book::factory()->count(2)->create();

        // 評価4のレビューを作成
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[0]->id,
            'rating' => 4,
        ]);

        // 評価5のレビューを作成
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[1]->id,
            'rating' => 5,
        ]);

        // 読書レポートにアクセス
        $response = $this->actingAs($user)->get(route('reports.index'));

        // 星4以上の書籍が表示されることを確認
        $response->assertViewHas('stats', function ($stats) use ($books) {
            $topRatedBooks = $stats['top_rated_books'];

            return count($topRatedBooks) === 2
                && collect($topRatedBooks)->pluck('id')->contains($books[0]->id)
                && collect($topRatedBooks)->pluck('id')->contains($books[1]->id);
        });
    }

    public function test_評価の高い順に表示される(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を3冊作成
        $books = Book::factory()->count(3)->create();

        // 評価4のレビューを作成
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[0]->id,
            'rating' => 4,
        ]);

        // 評価5のレビューを作成
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[1]->id,
            'rating' => 5,
        ]);

        // 評価3のレビューを作成
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[2]->id,
            'rating' => 3,
        ]);

        // 読書レポートにアクセス
        $response = $this->actingAs($user)->get(route('reports.index'));

        // 評価の高い順に表示されることを確認
        $response->assertViewHas('stats', function ($stats) use ($books) {
            $topRatedBooks = $stats['top_rated_books'];

            return collect($topRatedBooks)->pluck('id')->all() === [
                $books[1]->id,
                $books[0]->id,
            ];
        });
    }

    public function test_高評価書籍は最大5件まで表示される(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を6冊作成
        $books = Book::factory()->count(6)->create();

        // 1〜5冊目に評価5のレビューを作成
        foreach ($books as $index => $book) {
            Review::factory()->create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'rating' => 5,
            ]);
        }

        // 6冊目の書籍のレビューを評価4にする
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[5]->id,
            'rating' => 4,
        ]);

        // 読書レポートにアクセス
        $response = $this->actingAs($user)->get(route('reports.index'));

        // 高評価書籍が最大5件であることを確認
        $response->assertViewHas('stats', function ($stats) use ($books) {
            $topRatedBooks = $stats['top_rated_books'];

            return count($topRatedBooks) === 5
                && !collect($topRatedBooks)->pluck('id')->contains($books[5]->id);
        });
    }

    public function test_星4未満の書籍は表示されない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $books = Book::factory()->count(3)->create();

        // 評価3のレビューを書籍分作成
        foreach ($books as $index => $book) {
            Review::factory()->create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'rating' => 3,
            ]);
        }

        // 読書レポートを取得
        $response = $this->actingAs($user)->get(route('reports.index'));

        // 星4未満の書籍が表示されないことを確認
        $response->assertViewHas('stats', function ($stats) use ($books) {
            $topRatedBooks = $stats['top_rated_books'];

            return collect($topRatedBooks)
                ->pluck('id')
                ->intersect($books->pluck('id'))
                ->isEmpty();
        });
    }
}
