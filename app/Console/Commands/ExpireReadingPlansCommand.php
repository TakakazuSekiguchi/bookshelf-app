<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ExpireReadingPlansCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reading-plans:daily-batch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '読書計画の日次バッチ処理';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // 3日前のリマインダー
        $this->sendThreeDaysBeforeReminders();

        // 期日当日のリマインダー
        $this->sendDueDateReminders();

        // 期日超過のステータス更新
        $this->updateOverdueReadingPlans();

        // 期日から3日経過したリマインダー
        $this->sendThreeDaysAfterReminders();

        $this->info('日次バッチ処理が完了しました。');

        return self::SUCCESS;
    }

    // 期日超過のステータス更新
    public function updateOverdueReadingPlans()
    {
        // 期日を経過した進行中の読書計画を取得
        $readingPlans = ReadingPlan::query()
            ->where('status', ReadingPlanStatus::InProgress)
            ->whereDate('target_date', '<', Carbon::today())
            ->get();

        // ステータスを期日超過に変更
        foreach ($readingPlans as $plan) {
            $plan->update([
                'status' => ReadingPlanStatus::Overdue,
            ]);
        }

        $this->info('期日超過の読書計画のステータスを更新しました。');

        return self::SUCCESS;
    }

    // 3日前のリマインダー
    public function sendThreeDaysBeforeReminders()
    {
        // 3日前リマインダーの対象を取得
        $readingPlans = ReadingPlan::query()
            ->where('status', ReadingPlanStatus::InProgress)
            ->whereDate('target_date', Carbon::today()->addDays(3))
            ->get();

        // 対象の読書計画に通知を発行
        foreach ($readingPlans as $plan) {
            $plan->user->notify(
                new ReadingPlanReminderNotification(
                    $plan,
                    'three_days_before'
                )
            );
        }
    }

    // 期日当日のリマインダー
    public function sendDueDateReminders()
    {
        // 期日当日のリマインダー対象を取得
        $readingPlans = ReadingPlan::query()
            ->where('status', ReadingPlanStatus::InProgress)
            ->whereDate('target_date', Carbon::today())
            ->get();

        // 対象の読書計画に通知を発行
        foreach ($readingPlans as $plan) {
            $plan->user->notify(
                new ReadingPlanReminderNotification(
                    $plan,
                    'on_due_date'
                )
            );
        }
    }

    // 期日から3日経過したリマインダー
    public function sendThreeDaysAfterReminders()
    {
        // 期日から3日経過したリマインダー対象を取得
        $readingPlans = ReadingPlan::query()
            ->where('status', ReadingPlanStatus::Overdue)
            ->whereDate('target_date', Carbon::today()->subDays(3))
            ->get();

        // 対象の読書計画に通知を発行
        foreach ($readingPlans as $plan) {
            $plan->user->notify(
                new ReadingPlanReminderNotification(
                    $plan,
                    'three_days_after'
                )
            );
        }
    }
}