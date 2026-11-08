<?php

namespace Tests\Feature\Ranking;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Review;
use App\Models\User;

class RankingIndexTest extends TestCase
{
    use RefreshDatabase;

    protected $books;

    protected function setUp(): void
    {
        parent::setUp();

        // 書籍とレビュー評価の組み合わせ
        $rankingData = [
            'A' => [5, 5, 5], // 平均評価5
            'B' => [5, 5, 4], // 平均評価4.67
            'C' => [5, 5, 4, 4], // 平均評価4.5
            'D' => [5, 4, 4], // 平均評価4.33
            'E' => [4, 4, 4], // 平均評価4
            'F' => [3], // 平均評価3 ランキングに表示されない
            'G' => [], // 平均評価0 ランキングに表示されない
        ];

        $this->books = [];

        foreach ($rankingData as $title => $ratings) {

            // 書籍作成
            $book = Book::factory()->create([
                'title' => "書籍{$title}",
            ]);

            $this->books[$title] = $book;

            // レビュー作成
            foreach ($ratings as $rating) {
                Review::factory()->create([
                    'book_id' => $book->id,
                    'user_id' => User::factory()->create()->id,
                    'rating' => $rating,
                ]);
            }
        }
    }

    public function test_ランキングページを表示できる(): void
    {
        $response = $this->get(route('ranking.index'));

        $response->assertOk();
    }

    public function test_レビュー平均評価が高い順に表示される(): void
    {
        $response = $this->get(route('ranking.index'));

        $response->assertOk();

        $response->assertViewHas('rankedBooks', function ($rankedBooks) {
            $bookIds = $rankedBooks->pluck('id')->toArray();

            $expectedIds = [
                $this->books['A']->id,
                $this->books['B']->id,
                $this->books['C']->id,
                $this->books['D']->id,
                $this->books['E']->id,
            ];

            return $bookIds === $expectedIds;
        });
    }

    public function test_レビュー平均評価のTOP10の書籍が表示される()
    {
        // 平均評価4以上の書籍を6冊追加
        // これでsetUp()の平均評価4以上の書籍と合わせて、平均評価4以上の書籍の合計が11冊になる
        for ($i = 1; $i <= 6; $i++) {
            $book = Book::factory()->create([
                'title' => "追加書籍{$i}",
            ]);

            Review::factory()->create([
                'book_id' => $book->id,
                'user_id' => User::factory()->create()->id,
                'rating' => 4,
            ]);

            // 11位の書籍を保存
            if ($i === 6) {
                $eleventhBook = $book;
            }
        }

        $response = $this->get(route('ranking.index'));

        $response->assertOk();

        $response->assertViewHas('rankedBooks', function ($rankedBooks) use ($eleventhBook) {
            // 表示件数が10件であること
            $this->assertCount(10, $rankedBooks);

            // 11位の書籍が表示されていないこと
            $this->assertFalse(
                $rankedBooks->contains('id', $eleventhBook->id)
            );

            return true;
        });
    }

    public function test_レビュー平均評価が4以上の書籍のみ表示される()
    {
        $response = $this->get(route('ranking.index'));

        $response->assertOk();

        $response->assertViewHas('rankedBooks', function ($rankedBooks) {
            // 平均評価4以上の書籍が表示されていること
            $this->assertTrue($rankedBooks->contains('id', $this->books['A']->id));
            $this->assertTrue($rankedBooks->contains('id', $this->books['B']->id));
            $this->assertTrue($rankedBooks->contains('id', $this->books['C']->id));
            $this->assertTrue($rankedBooks->contains('id', $this->books['D']->id));
            $this->assertTrue($rankedBooks->contains('id', $this->books['E']->id));

            // 平均評価4未満の書籍が表示されていないこと
            $this->assertFalse($rankedBooks->contains('id', $this->books['F']->id));

            return true;
        });
    }

    public function test_レビューがない書籍はランキングに表示されない()
    {
        $response = $this->get(route('ranking.index'));

        $response->assertOk();

        $response->assertViewHas('rankedBooks', function ($rankedBooks) {
            // レビューがない書籍が表示されていないこと
            $this->assertFalse($rankedBooks->contains('id', $this->books['G']->id));

            return true;
        });
    }
}
