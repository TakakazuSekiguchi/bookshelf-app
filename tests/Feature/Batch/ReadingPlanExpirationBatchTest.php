<?php

namespace Tests\Feature\Batch;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminderNotification;
use App\Enums\ReadingPlanStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;

class ReadingPlanExpirationBatchTest extends TestCase
{
    use RefreshDatabase;

    // 毎回日時を解除
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    // 正常系
    public function test_期日を経過した進行中の読書計画が期日超過に変更される(): void
    {
        // 通知をFake
        Notification::fake();

        // 本日の日付を固定
        Carbon::setTestNow(Carbon::parse('2026-11-15'));

        // ユーザーを作成
        $user = User::factory()->create();

        // 期日が1日過ぎている読書計画を作成（期日：2026-11-14）
        // statusはまだ「in_progress」のまま
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => now()->subDays(1)->toDateString(),
            'status' => 'in_progress',
        ]);

        // バッチを実行
        Artisan::call('reading-plans:daily-batch');

        // DBが更新されていることを確認
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => 'overdue',
        ]);
    }

    // 更新対象外
    public function test_期日前の読書計画は期日超過に変更されない(): void
    {
        // 通知をFake
        Notification::fake();

        // 本日の日付を固定
        Carbon::setTestNow(Carbon::parse('2026-11-15'));

        // ユーザーを作成
        $user = User::factory()->create();
        
        // 期日が3日後の読書計画を作成（期日：2026-11-18）
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => now()->addDays(3)->toDateString(),
            'status' => 'in_progress',
        ]);

        // バッチを実行
        Artisan::call('reading-plans:daily-batch');

        // DBが更新されていないことを確認
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_期日当日の読書計画は期日超過に変更されない(): void
    {
        // 通知をFake
        Notification::fake();

        // 本日の日付を固定
        Carbon::setTestNow(Carbon::parse('2026-11-15'));

        // ユーザーを作成
        $user = User::factory()->create();
        
        // 期日が当日の読書計画を作成（期日：2026-11-15）
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => now()->toDateString(),
            'status' => 'in_progress',
        ]);

        // バッチを実行
        Artisan::call('reading-plans:daily-batch');

        // DBが更新されていないことを確認
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_読了済みの読書計画は期日超過に変更されない(): void
    {
        // 通知をFake
        Notification::fake();

        // 本日の日付を固定
        Carbon::setTestNow(Carbon::parse('2026-11-15'));

        // ユーザーを作成
        $user = User::factory()->create();
        
        // 「期日が3日後、かつ、読了済み」の読書計画を作成（期日：2026-11-18）
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => now()->addDays(3)->toDateString(),
            'status' => 'completed',
        ]);

        // バッチを実行
        Artisan::call('reading-plans:daily-batch');

        // DBが更新されていないことを確認
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => 'completed',
        ]);
    }

    public function test_既に期日超過の読書計画には通知が作成されない(): void
    {
        // 通知をFake
        Notification::fake();

        // 本日の日付を固定
        Carbon::setTestNow(Carbon::parse('2026-11-15'));

        // ユーザーを作成
        $user = User::factory()->create();
        
        // 期日が2日過ぎている読書計画を作成（期日：2026-11-13）
        // statusは既に「overdue」に変更されている（2026-11-14にstatusが変更された）
        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => now()->subDays(2)->toDateString(),
            'status' => 'overdue',
        ]);

        // バッチを実行
        Artisan::call('reading-plans:daily-batch');

        // 通知が送信されていないことを確認
        Notification::assertNothingSent();
    }
}
