<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ReadingPlanSeeder extends Seeder
{
    public function run(): void
    {
        // ユーザーを取得
        $yamada = User::where('name', '山田太郎')->firstOrFail();
        $suzuki = User::where('name', '鈴木花子')->firstOrFail();

        // 書籍を取得
        $books = Book::all();

        // 読書計画に必要な書籍数を確認
        if ($books->count() < 6) {
            throw new \RuntimeException(
                'ReadingPlanSeederの実行には6冊以上の書籍が必要です。'
            );
        }

        // それぞれ異なる書籍を使用
        $books = $books->take(6)->values();

        // 今日を基準に期日を設定
        $today = Carbon::today();

        // 山田太郎：3日後が期日（3日前リマインダー対象）
        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[0]->id,
            'target_date' => $today->copy()->addDays(3),
            'status' => ReadingPlanStatus::InProgress->value,
        ]);

        // 山田太郎：今日が期日（当日リマインダー対象）
        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[1]->id,
            'target_date' => $today->copy(),
            'status' => ReadingPlanStatus::InProgress->value,
        ]);

        // 山田太郎：3日前が期日（期限超過・再エンゲージメント対象）
        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[2]->id,
            'target_date' => $today->copy()->subDays(3),
            'status' => ReadingPlanStatus::InProgress->value,
        ]);

        // 山田太郎：7日後が期日（リマインダー対象外）
        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[3]->id,
            'target_date' => $today->copy()->addDays(7),
            'status' => ReadingPlanStatus::InProgress->value,
        ]);

        // 山田太郎：読了済み（期限超過処理の対象外）
        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[4]->id,
            'target_date' => $today->copy()->subDays(10),
            'status' => ReadingPlanStatus::Completed->value,
            'completed_at' => $today->copy()->subDays(5),
        ]);

        // 鈴木花子：他ユーザーの認可テスト用
        ReadingPlan::create([
            'user_id' => $suzuki->id,
            'book_id' => $books[5]->id,
            'target_date' => $today->copy()->addDays(5),
            'status' => ReadingPlanStatus::InProgress->value,
        ]);
    }
}