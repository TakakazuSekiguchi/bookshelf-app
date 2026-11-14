<?php

namespace Tests\Feature\ReadingReport;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
// use App\Models\Genre;
use App\Models\Review;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Enums\ReadingPlanStatus;

class ReadingReportSummaryTest extends TestCase
{
    use RefreshDatabase;

    // 基本サマリー
    public function test_総レビュー数が正しく表示される(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // レビューを3件作成
        Review::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        // 読書レポートにアクセス
        $response = $this->actingAs($user)->get(route('reports.index'));

        // 総レビュー数が正しいことを確認
        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['total_reviews'] === 3;
        });
    }

    public function test_読了冊数が正しく表示される(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を3冊作成
        $books = Book::factory()->count(3)->create();

        // 読了済みの読書計画を3件作成
        foreach ($books as $book) {
            ReadingPlan::factory()->create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'status' => ReadingPlanStatus::Completed->value,
            ]);
        }

        // 読書レポートにアクセス
        $response = $this->actingAs($user)->get(route('reports.index'));

        // 読了冊数が正しいことを確認
        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['books_read'] === 3;
        });
    }

    public function test_同じ書籍の読書計画が複数あっても1冊として集計される(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を2冊作成
        $books = Book::factory()->count(2)->create();

        // 1冊目の書籍に読了済みの読書計画を2件作成
        ReadingPlan::factory()->count(2)->create([
            'user_id' => $user->id,
            'book_id' => $books[0]->id,
            'status' => ReadingPlanStatus::Completed->value,
        ]);

        // 2冊目の書籍に読了済みの読書計画を1件作成
        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[1]->id,
            'status' => ReadingPlanStatus::Completed->value,
        ]);

        // 読書レポートにアクセス
        $response = $this->actingAs($user)->get(route('reports.index'));

        // 読了冊数が2冊になることを確認
        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['books_read'] === 2;
        });
    }

    public function test_平均評価点が正しく表示される(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // レビューを3件作成（評価：5、4、3）
        Review::factory()->create([
            'user_id' => $user->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'rating' => 4,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'rating' => 3,
        ]);

        // 読書レポートにアクセス
        $response = $this->actingAs($user)->get(route('reports.index'));

        // 平均評価点が正しいことを確認
        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['average_rating'] === 4.0;
        });
    }
}
