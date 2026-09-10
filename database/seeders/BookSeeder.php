<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //登録したユーザー
        $user = User::first(); //山田太郎

        //ジャンル
        $novel = Genre::where('name', '小説')->first();
        $business = Genre::where('name', 'ビジネス')->first();
        $technical = Genre::where('name', '技術書')->first();
        $selfHelp = Genre::where('name', '自己啓発')->first();
        $essay = Genre::where('name', 'エッセイ')->first();
        $history = Genre::where('name', '歴史')->first();
        $science = Genre::where('name', '科学')->first();
        $art = Genre::where('name', '芸術')->first();
        $cooking = Genre::where('name', '料理')->first();
        $travel = Genre::where('name', '旅行')->first();

        // 1. 吾輩は猫である
        $book = Book::firstOrCreate(
            ['isbn_13' => '9784101010014'],
            [
                'user_id' => $user->id,
                'title' => '吾輩は猫である',
                'author' => '夏目漱石',
                'published_date' => '1905-01-01',
                'description' => '猫の視点から人間社会を描いた小説。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=1',
            ]
        );
        $book->genres()->sync([$novel->id]);

        // 2. 人を動かす
        $book = Book::firstOrCreate(
            ['isbn_13' => '9784422100524'],
            [
                'user_id' => $user->id,
                'title' => '人を動かす',
                'author' => 'D・カーネギー',
                'published_date' => '1936-10-01',
                'description' => '人間関係を円滑にし、人を動かすための原則や考え方を解説した自己啓発書。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=2',
            ]
        );
        $book->genres()->sync([
            $business->id,
            $selfHelp->id,
        ]);

        // 3. リーダブルコード
        $book = Book::firstOrCreate(
            ['isbn_13' => '9784873115658'],
            [
                'user_id' => $user->id,
                'title' => 'リーダブルコード',
                'author' => 'Dustin Boswell',
                'published_date' => '2012-06-23',
                'description' => '読みやすく、理解しやすいコードを書くための考え方やテクニックを解説した技術書。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=3',
            ]
        );
        $book->genres()->sync([
            $technical->id,
        ]);

        // 4. 7つの習慣
        $book = Book::firstOrCreate(
            ['isbn_13' => '9784863940246'],
            [
                'user_id' => $user->id,
                'title' => '7つの習慣',
                'author' => 'スティーブン・R・コヴィー',
                'published_date' => '2013-08-30',
                'description' => '個人の成長や人間関係を改善するための7つの習慣を体系的に解説したビジネス・自己啓発書。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=4',
            ]
        );
        $book->genres()->sync([
            $business->id,
            $selfHelp->id,
        ]);

        // 5. 坊っちゃん
        $book = Book::firstOrCreate(
            ['isbn_13' => '9784101010021'],
            [
                'user_id' => $user->id,
                'title' => '坊っちゃん',
                'author' => '夏目漱石',
                'published_date' => '1906-04-01',
                'description' => '正義感の強い主人公が教師として赴任した学校で巻き起こる出来事を描いた小説。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=5',
            ]
        );
        $book->genres()->sync([
            $novel->id,
        ]);

        // 6. サピエンス全史
        $book = Book::firstOrCreate(
            ['isbn_13' => '9784309226712'],
            [
                'user_id' => $user->id,
                'title' => 'サピエンス全史',
                'author' => 'ユヴァル・ノア・ハラリ',
                'published_date' => '2016-09-08',
                'description' => '人類の誕生から現代までの歴史を、生物学や社会学などの視点から考察した作品。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=6',
            ]
        );
        $book->genres()->sync([
            $history->id,
            $science->id,
        ]);

        // 7. Clean Code
        $book = Book::firstOrCreate(
            ['isbn_13' => '9784048930598'],
            [
                'user_id' => $user->id,
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'published_date' => '2017-12-18',
                'description' => '保守性や可読性に優れた、品質の高いコードを書くための原則や実践方法を解説した技術書。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=7',
            ]
        );
        $book->genres()->sync([
            $technical->id,
        ]);

        // 8. 嫌われる勇気
        $book = Book::firstOrCreate(
            ['isbn_13' => '9784478025819'],
            [
                'user_id' => $user->id,
                'title' => '嫌われる勇気',
                'author' => '岸見一郎・古賀史健',
                'published_date' => '2013-12-13',
                'description' => 'アドラー心理学をもとに、他者との関係や自分らしく生きるための考え方を対話形式で解説した自己啓発書。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=8',
            ]
        );
        $book->genres()->sync([
            $selfHelp->id,
        ]);

        // 9. 火花
        $book = Book::firstOrCreate(
            ['isbn_13' => '9784163902302'],
            [
                'user_id' => $user->id,
                'title' => '火花',
                'author' => '又吉直樹',
                'published_date' => '2015-03-11',
                'description' => 'お笑い芸人を目指す若者たちの交流や葛藤を描いた小説。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=9',
            ]
        );
        $book->genres()->sync([
            $novel->id,
        ]);

        // 10. FACTFULNESS
        $book = Book::firstOrCreate(
            ['isbn_13' => '9784822289607'],
            [
                'user_id' => $user->id,
                'title' => 'FACTFULNESS',
                'author' => 'ハンス・ロスリング',
                'published_date' => '2019-01-11',
                'description' => '世界を取り巻く状況をデータに基づいて正しく理解するための考え方を解説した書籍。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=10',
            ]
        );
        $book->genres()->sync([
            $business->id,
            $science->id,
        ]);

        // 11. コンテナ物語
        $book = Book::firstOrCreate(
            ['isbn_13' => '9784822251468'],
            [
                'user_id' => $user->id,
                'title' => 'コンテナ物語',
                'author' => 'マルク・レビンソン',
                'published_date' => '2007-01-18',
                'description' => 'コンテナの登場が世界の貿易や物流に与えた影響を、その歴史とともに描いた作品。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=11',
            ]
        );
        $book->genres()->sync([
            $business->id,
            $history->id,
        ]);
    }
}
