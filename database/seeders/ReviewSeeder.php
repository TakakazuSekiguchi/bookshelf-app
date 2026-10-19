<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Book;
use App\Models\Review;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ユーザー5人すべてを取得
        $users = User::all();

        // 書籍11冊すべてを取得
        $books = Book::all();

        // 評価別コメントテンプレート
        $comments = [
            5 => [
                '素晴らしい本でした！',
                '人生が変わりました。',
                '何度も読み返しています。',
            ],
            4 => [
                'とても参考になりました。',
                '読みやすくておすすめです。',
                '期待通りの内容でした。',
            ],
            3 => [
                '普通でした。',
                '可もなく不可もなく。',
                '期待したほどではなかった。',
            ],
            2 => [
                '少し期待外れでした。',
                '内容が薄い印象。',
                'もう少し深掘りしてほしかった。',
            ],
            1 => [
                '残念ながら合いませんでした。',
                '期待と違いました。',
            ],
        ];

        foreach ($books as $book) {
            // 書籍ごとのレビュー件数を2〜4件に設定
            $reviewCount = rand(2, 4);

            // 投稿者をランダムに選出
            $reviewUsers = $users->random($reviewCount);

            foreach ($reviewUsers as $user) {
                // 評価を1〜5からランダムに選択
                $rating = rand(1, 5);

                Review::create([
                    'user_id' => $user->id,
                    'book_id' => $book->id,
                    'rating' => $rating,
                    'comment' => collect($comments[$rating])->random(),
                ]);
            }
        }
    }
}