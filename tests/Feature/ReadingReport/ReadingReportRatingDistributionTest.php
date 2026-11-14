<?php

namespace Tests\Feature\ReadingReport;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
// use App\Models\Book;
// use App\Models\Genre;
use App\Models\Review;
// use App\Models\ReadingPlan;
use App\Models\User;
// use App\Enums\ReadingPlanStatus;

class ReadingReportRatingDistributionTest extends TestCase
{
    use RefreshDatabase;

    // 評価分布
    public function test_評価分布が正しく表示される(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 評価1のレビューを1件作成
        Review::factory()->create([
            'user_id' => $user->id,
            'rating' => 1,
        ]);

        // 評価2のレビューを2件作成
        Review::factory()->count(2)->create([
            'user_id' => $user->id,
            'rating' => 2,
        ]);

        // 評価3のレビューを3件作成
        Review::factory()->count(3)->create([
            'user_id' => $user->id,
            'rating' => 3,
        ]);

        // 評価4のレビューを2件作成
        Review::factory()->count(2)->create([
            'user_id' => $user->id,
            'rating' => 4,
        ]);

        // 評価5のレビューを1件作成
        Review::factory()->count(1)->create([
            'user_id' => $user->id,
            'rating' => 5,
        ]);

        // 読書レポートにアクセス
        $response = $this->actingAs($user)->get(route('reports.index'));

        // 評価分布が正しいことを確認
        $response->assertViewHas('stats', function ($stats) {
            return $stats['rating_distribution']->all() === [
                0 => 1,
                1 => 2,
                2 => 3,
                3 => 2,
                4 => 1,
            ];
        });
    }

    public function test_レビューが存在しない評価の件数は0になる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 評価1のレビューを2件作成
        Review::factory()->count(2)->create([
            'user_id' => $user->id,
            'rating' => 1,
        ]);

        // 評価3のレビューを3件作成
        Review::factory()->count(3)->create([
            'user_id' => $user->id,
            'rating' => 3,
        ]);

        // 評価5のレビューを1件作成
        Review::factory()->create([
            'user_id' => $user->id,
            'rating' => 5,
        ]);

        // 読書レポートにアクセス
        $response = $this->actingAs($user)->get(route('reports.index'));

        // 評価分布が正しいことを確認
        $response->assertViewHas('stats', function ($stats) {
            return $stats['rating_distribution']->all() === [
                0 => 2,
                1 => 0,
                2 => 3,
                3 => 0,
                4 => 1,
            ];
        });
    }
}
