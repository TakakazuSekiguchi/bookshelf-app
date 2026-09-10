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

        // 書籍1：3件
        Review::create([
            'user_id' => $users[0]->id,
            'book_id' => $books[0]->id,
            'rating' => 5,
            'comment' => 'とても面白く、一気に最後まで読んでしまいました。',
        ]);

        Review::create([
            'user_id' => $users[1]->id,
            'book_id' => $books[0]->id,
            'rating' => 4,
            'comment' => 'ストーリーが分かりやすく、楽しく読めました。',
        ]);

        Review::create([
            'user_id' => $users[2]->id,
            'book_id' => $books[0]->id,
            'rating' => 5,
            'comment' => '登場人物の描写が丁寧で、とても印象に残りました。',
        ]);

        // 書籍2：3件
        Review::create([
            'user_id' => $users[0]->id,
            'book_id' => $books[1]->id,
            'rating' => 4,
            'comment' => '読みやすい文章で、内容にも引き込まれました。',
        ]);

        Review::create([
            'user_id' => $users[2]->id,
            'book_id' => $books[1]->id,
            'rating' => 5,
            'comment' => '期待していた以上に面白く、満足できる作品でした。',
        ]);

        Review::create([
            'user_id' => $users[3]->id,
            'book_id' => $books[1]->id,
            'rating' => 3,
            'comment' => '面白かったですが、少し展開が早く感じました。',
        ]);

        // 書籍3：3件
        Review::create([
            'user_id' => $users[1]->id,
            'book_id' => $books[2]->id,
            'rating' => 5,
            'comment' => 'テーマが興味深く、最後まで楽しく読めました。',
        ]);

        Review::create([
            'user_id' => $users[3]->id,
            'book_id' => $books[2]->id,
            'rating' => 4,
            'comment' => '内容がしっかりしていて、読み応えがありました。',
        ]);

        Review::create([
            'user_id' => $users[4]->id,
            'book_id' => $books[2]->id,
            'rating' => 4,
            'comment' => '登場人物に共感できる部分が多く、楽しめました。',
        ]);

        // 書籍4：3件
        Review::create([
            'user_id' => $users[0]->id,
            'book_id' => $books[3]->id,
            'rating' => 4,
            'comment' => 'テンポが良く、最後まで飽きずに読めました。',
        ]);

        Review::create([
            'user_id' => $users[2]->id,
            'book_id' => $books[3]->id,
            'rating' => 3,
            'comment' => '面白い内容でしたが、少し難しい部分もありました。',
        ]);

        Review::create([
            'user_id' => $users[4]->id,
            'book_id' => $books[3]->id,
            'rating' => 5,
            'comment' => 'とても読み応えがあり、何度でも読み返したい作品です。',
        ]);

        // 書籍5：3件
        Review::create([
            'user_id' => $users[1]->id,
            'book_id' => $books[4]->id,
            'rating' => 5,
            'comment' => '内容が面白く、時間を忘れて読み進めました。',
        ]);

        Review::create([
            'user_id' => $users[2]->id,
            'book_id' => $books[4]->id,
            'rating' => 4,
            'comment' => '文章が読みやすく、ストーリーにも魅力を感じました。',
        ]);

        Review::create([
            'user_id' => $users[3]->id,
            'book_id' => $books[4]->id,
            'rating' => 3,
            'comment' => '興味深い作品でしたが、少し長く感じました。',
        ]);

        // 書籍6：3件
        Review::create([
            'user_id' => $users[0]->id,
            'book_id' => $books[5]->id,
            'rating' => 5,
            'comment' => 'とても感動しました。印象に残る場面が多かったです。',
        ]);

        Review::create([
            'user_id' => $users[3]->id,
            'book_id' => $books[5]->id,
            'rating' => 4,
            'comment' => 'ストーリーがしっかりしていて面白かったです。',
        ]);

        Review::create([
            'user_id' => $users[4]->id,
            'book_id' => $books[5]->id,
            'rating' => 5,
            'comment' => '登場人物が魅力的で、最後まで楽しめました。',
        ]);

        // 書籍7：3件
        Review::create([
            'user_id' => $users[1]->id,
            'book_id' => $books[6]->id,
            'rating' => 4,
            'comment' => '分かりやすい内容で、楽しく読むことができました。',
        ]);

        Review::create([
            'user_id' => $users[2]->id,
            'book_id' => $books[6]->id,
            'rating' => 5,
            'comment' => '非常に面白く、読後感も良かったです。',
        ]);

        Review::create([
            'user_id' => $users[4]->id,
            'book_id' => $books[6]->id,
            'rating' => 3,
            'comment' => '内容は面白かったですが、少し展開が複雑でした。',
        ]);

        // 書籍8：3件
        Review::create([
            'user_id' => $users[0]->id,
            'book_id' => $books[7]->id,
            'rating' => 5,
            'comment' => '最後まで興味を持って読むことができました。',
        ]);

        Review::create([
            'user_id' => $users[1]->id,
            'book_id' => $books[7]->id,
            'rating' => 4,
            'comment' => '登場人物それぞれの個性が出ていて面白かったです。',
        ]);

        Review::create([
            'user_id' => $users[3]->id,
            'book_id' => $books[7]->id,
            'rating' => 4,
            'comment' => '読みやすく、ストーリーもよくまとまっていました。',
        ]);

        // 書籍9：3件
        Review::create([
            'user_id' => $users[2]->id,
            'book_id' => $books[8]->id,
            'rating' => 5,
            'comment' => 'とても面白い作品で、最後まで夢中になりました。',
        ]);

        Review::create([
            'user_id' => $users[3]->id,
            'book_id' => $books[8]->id,
            'rating' => 3,
            'comment' => '面白かったですが、少し内容が難しく感じました。',
        ]);

        Review::create([
            'user_id' => $users[4]->id,
            'book_id' => $books[8]->id,
            'rating' => 4,
            'comment' => '独特な世界観があり、楽しむことができました。',
        ]);

        // 書籍10：3件
        Review::create([
            'user_id' => $users[0]->id,
            'book_id' => $books[9]->id,
            'rating' => 4,
            'comment' => 'ストーリー展開が面白く、読みやすかったです。',
        ]);

        Review::create([
            'user_id' => $users[2]->id,
            'book_id' => $books[9]->id,
            'rating' => 5,
            'comment' => 'とても完成度が高く、最後まで楽しめました。',
        ]);

        Review::create([
            'user_id' => $users[4]->id,
            'book_id' => $books[9]->id,
            'rating' => 4,
            'comment' => '読み終わった後も内容について考えさせられました。',
        ]);

        // 書籍11：2件
        Review::create([
            'user_id' => $users[1]->id,
            'book_id' => $books[10]->id,
            'rating' => 5,
            'comment' => '非常に面白く、最後まで楽しく読むことができました。',
        ]);

        Review::create([
            'user_id' => $users[3]->id,
            'book_id' => $books[10]->id,
            'rating' => 4,
            'comment' => '内容が分かりやすく、読み応えのある作品でした。',
        ]);
    }
}