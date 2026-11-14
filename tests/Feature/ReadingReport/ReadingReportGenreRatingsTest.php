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

class ReadingReportGenreRatingsTest extends TestCase
{
    use RefreshDatabase;

    // ジャンル別評価傾向TOP5
    public function test_ジャンルごとの平均評価が正しく表示される(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを5つ作成
        $genres = Genre::factory()->count(5)->create();

        // 書籍を作成
        $books = Book::factory()->count(5)->create();

        // 書籍とジャンルを紐づけ
        // 各書籍に対して、異なるジャンルを一つずつ紐づけ、登録した書籍のジャンルがすべて異なる状態にする
        for($i = 0; $i < 5; $i++){
            $books[$i]->genres()->attach($genres[$i]->id);    
        }

        // ジャンルごとの平均評価を設定するため、各書籍に2件ずつレビューを作成
        // 平均評価4.5
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[0]->id,
            'rating' => 5,
        ]);
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[0]->id,
            'rating' => 4,
        ]);

        // 平均評価4
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[1]->id,
            'rating' => 4,
        ]);
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[1]->id,
            'rating' => 4,
        ]);

        // 平均評価3.5
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[2]->id,
            'rating' => 4,
        ]);
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[2]->id,
            'rating' => 3,
        ]);

        // 平均評価3
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[3]->id,
            'rating' => 3,
        ]);
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[3]->id,
            'rating' => 3,
        ]);

        // 平均評価2.5
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[4]->id,
            'rating' => 3,
        ]);
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[4]->id,
            'rating' => 2,
        ]);

        // 読書レポートを取得
        $response = $this->actingAs($user)->get(route('reports.index'));

        // ジャンルごとの平均評価が正しく表示されることを確認
        $response->assertViewHas('stats', function ($stats) use ($genres) {
            $genreRatings = collect($stats['genre_ratings'])->keyBy('id');

            return $genreRatings[$genres[0]->id]['average_rating'] === 4.5
                && $genreRatings[$genres[1]->id]['average_rating'] === 4.0
                && $genreRatings[$genres[2]->id]['average_rating'] === 3.5
                && $genreRatings[$genres[3]->id]['average_rating'] === 3.0
                && $genreRatings[$genres[4]->id]['average_rating'] === 2.5;
        });
    }

    public function test_ジャンルごとのレビュー件数が正しく表示される(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを5つ作成
        $genres = Genre::factory()->count(5)->create();

        // 書籍を5つ作成
        $books = Book::factory()->count(5)->create();

        // 書籍とジャンルを紐づけ
        // 各書籍に異なるジャンルを一つずつ紐づける
        for ($i = 0; $i < 5; $i++) {
            $books[$i]->genres()->attach($genres[$i]->id);
        }

        // ジャンル1：レビュー件数1件
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[0]->id,
        ]);

        // ジャンル2：レビュー件数2件
        Review::factory()->count(2)->create([
            'user_id' => $user->id,
            'book_id' => $books[1]->id,
        ]);

        // ジャンル3：レビュー件数3件
        Review::factory()->count(3)->create([
            'user_id' => $user->id,
            'book_id' => $books[2]->id,
        ]);

        // ジャンル4：レビュー件数4件
        Review::factory()->count(4)->create([
            'user_id' => $user->id,
            'book_id' => $books[3]->id,
        ]);

        // ジャンル5：レビュー件数5件
        Review::factory()->count(5)->create([
            'user_id' => $user->id,
            'book_id' => $books[4]->id,
        ]);

        // 読書レポートを取得
        $response = $this->actingAs($user)->get(route('reports.index'));

        // ジャンルごとのレビュー件数が正しく表示されることを確認
        $response->assertViewHas('stats', function ($stats) use ($genres) {
            $genreRatings = collect($stats['genre_ratings'])->keyBy('id');

            return $genreRatings[$genres[0]->id]['count'] === 1
                && $genreRatings[$genres[1]->id]['count'] === 2
                && $genreRatings[$genres[2]->id]['count'] === 3
                && $genreRatings[$genres[3]->id]['count'] === 4
                && $genreRatings[$genres[4]->id]['count'] === 5;
        });
    }

    public function test_平均評価の高い順に表示される(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを5つ作成
        $genres = Genre::factory()->count(5)->create();

        // 書籍を5つ作成
        $books = Book::factory()->count(5)->create();

        // 書籍とジャンルを紐づけ
        for ($i = 0; $i < 5; $i++) {
            $books[$i]->genres()->attach($genres[$i]->id);
        }

        // ジャンル1：平均評価4.5
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[0]->id,
            'rating' => 5,
        ]);
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[0]->id,
            'rating' => 4,
        ]);

        // ジャンル2：平均評価4.0
        Review::factory()->count(2)->create([
            'user_id' => $user->id,
            'book_id' => $books[1]->id,
            'rating' => 4,
        ]);

        // ジャンル3：平均評価3.5
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[2]->id,
            'rating' => 4,
        ]);
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[2]->id,
            'rating' => 3,
        ]);

        // ジャンル4：平均評価3.0
        Review::factory()->count(2)->create([
            'user_id' => $user->id,
            'book_id' => $books[3]->id,
            'rating' => 3,
        ]);

        // ジャンル5：平均評価2.5
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[4]->id,
            'rating' => 3,
        ]);
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[4]->id,
            'rating' => 2,
        ]);

        // 読書レポートを取得
        $response = $this->actingAs($user)->get(route('reports.index'));

        // 平均評価の高い順に表示されることを確認
        $response->assertViewHas('stats', function ($stats) use ($genres) {
            $genreRatings = collect($stats['genre_ratings']);

            return $genreRatings->pluck('id')->all() === [
                $genres[0]->id,
                $genres[1]->id,
                $genres[2]->id,
                $genres[3]->id,
                $genres[4]->id,
            ];
        });
    }

    public function test_ジャンル別評価傾向は最大5件まで表示される(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // ジャンルを6つ作成
        $genres = Genre::factory()->count(6)->create();

        // 書籍を6つ作成
        $books = Book::factory()->count(6)->create();

        // 書籍とジャンルを紐づけ
        // 各書籍に異なるジャンルを一つずつ紐づける
        for ($i = 0; $i < 6; $i++) {
            $books[$i]->genres()->attach($genres[$i]->id);
        }

        // ジャンルごとに異なる平均評価のレビューを作成
        $ratings = [
            [5, 5], // 平均評価5.0
            [5, 4], // 平均評価4.5
            [4, 4], // 平均評価4.0
            [4, 3], // 平均評価3.5
            [3, 3], // 平均評価3.0
            [2, 2], // 平均評価2.0
        ];

        foreach ($ratings as $index => $ratingValues) {
            foreach ($ratingValues as $rating) {
                Review::factory()->create([
                    'user_id' => $user->id,
                    'book_id' => $books[$index]->id,
                    'rating' => $rating,
                ]);
            }
        }

        // 読書レポートを取得
        $response = $this->actingAs($user)->get(route('reports.index'));

        // ジャンル別評価傾向が最大5件まで表示されることを確認
        $response->assertViewHas('stats', function ($stats) use ($genres) {
            $genreRatings = collect($stats['genre_ratings']);

            return $genreRatings->count() === 5
                && !$genreRatings->pluck('id')->contains($genres[5]->id);
        });
    }
}
