<?php

namespace Tests\Feature\Batch;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminderNotification;
use App\Enums\ReadingPlanStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;

class ReadingPlanReminderBatchTest extends TestCase
{
    use RefreshDatabase;

    // 毎回日時を解除
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    // 正常系
    public function test_期日3日前の読書計画にリマインダー通知が作成される(): void
    {
        // 通知をFake
        Notification::fake();

        // 本日の日付を固定
        Carbon::setTestNow(Carbon::parse('2026-11-15'));

        // ユーザーを作成
        $user = User::factory()->create();

        // 期日3日前の読書計画を作成（期日：2026-11-18）
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => now()->addDays(3)->toDateString(),
            'status' => 'in_progress',
        ]);

        // バッチを実行
        Artisan::call('reading-plans:daily-batch');

        // 通知が作成され、内容も正しいことを確認
        // 「本日：2026-11-15」に対して「期日：2026-11-18」としているので【期日3日前】の読書計画を対象に通知が作成されている
        Notification::assertSentTo(
            $user,
            ReadingPlanReminderNotification::class,
            function ($notification, $channels) use ($user, $readingPlan) {

                $data = $notification->toDatabase($user);

                $this->assertSame('three_days_before', $data['timing']);

                $this->assertSame(
                    '読書計画リマインド — 期限まであと 3 日',
                    $data['title']
                );

                $this->assertSame(
                    "「{$readingPlan->book->title}」の期限まで残り 3 日です。引き続き読書を進めましょう。",
                    $data['body']
                );

                return true;
            }
        );
    }

    public function test_期日当日の読書計画にリマインダー通知が作成される(): void
    {
        // 通知をFake
        Notification::fake();

        // 本日の日付を固定
        Carbon::setTestNow(Carbon::parse('2026-11-15'));

        // ユーザーを作成
        $user = User::factory()->create();

        // 期日当日の読書計画を作成（期日：2026-11-15）
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => now()->toDateString(),
            'status' => 'in_progress',
        ]);

        // バッチを実行
        Artisan::call('reading-plans:daily-batch');

        // 通知が作成され、内容も正しいことを確認
        // 「本日：2026-11-15」に対して「期日：2026-11-15」としているので【期日当日】の読書計画を対象に通知が作成されている
        Notification::assertSentTo(
            $user,
            ReadingPlanReminderNotification::class,
            function ($notification, $channels) use ($user, $readingPlan) {

                $data = $notification->toDatabase($user);

                $this->assertSame('on_due_date', $data['timing']);

                $this->assertSame(
                    '読書計画 — 本日が期限',
                    $data['title']
                );

                $this->assertSame(
                    "「{$readingPlan->book->title}」は本日が期限です。読了済みなら完了登録を、もう少し必要なら期限を変更してください。",
                    $data['body']
                );

                return true;
            }
        );
    }

    public function test_期日から3日経過した読書計画にリマインダー通知が作成される(): void
    {
        // 通知をFake
        Notification::fake();

        // 本日の日付を固定
        Carbon::setTestNow(Carbon::parse('2026-11-15'));

        // ユーザーを作成
        $user = User::factory()->create();

        // 期日3日後の読書計画を作成（期日：2026-11-12）
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => now()->subDays(3)->toDateString(),
            'status' => 'in_progress',
        ]);

        // バッチを実行
        Artisan::call('reading-plans:daily-batch');

        // 通知が作成され、内容も正しいことを確認
        // 「本日：2026-11-15」に対して「期日：2026-11-12」としているので【期日3日後】の読書計画を対象に通知が作成されている
        Notification::assertSentTo(
            $user,
            ReadingPlanReminderNotification::class,
            function ($notification, $channels) use ($user, $readingPlan) {

                $data = $notification->toDatabase($user);

                $this->assertSame('three_days_after', $data['timing']);

                $this->assertSame(
                    '読書計画 — 期限超過 3 日経過',
                    $data['title']
                );

                $this->assertSame(
                    "「{$readingPlan->book->title}」の期限から 3 日が経過しました。読了済みなら完了登録、続けるなら期限を変更してください。",
                    $data['body']
                );

                return true;
            }
        );
    }

    // 異常系
    public function test_リマインダー対象日ではない読書計画には通知が作成されない(): void
    {
        // 通知をFake
        Notification::fake();

        // 本日の日付を固定
        Carbon::setTestNow(Carbon::parse('2026-11-15'));

        // ユーザーを作成
        $user = User::factory()->create();

        // 期日10日前の読書計画を作成（期日：2026-11-05）
        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => now()->subDays(10)->toDateString(),
            'status' => 'in_progress',
        ]);

        // 期日5日後の読書計画を作成（期日：2026-11-20）
        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => now()->addDays(5)->toDateString(),
            'status' => 'in_progress',
        ]);

        // バッチを実行
        Artisan::call('reading-plans:daily-batch');

        // 通知が送信されていないことを確認
        Notification::assertNothingSent();
    }

    public function test_読了済みの読書計画には通知が作成されない(): void
    {
        // 通知をFake
        Notification::fake();

        // 本日の日付を固定
        Carbon::setTestNow(Carbon::parse('2026-11-15'));

        // ユーザーを作成
        $user = User::factory()->create();

        // 「期日3日前、かつ、読了済み」の読書計画を作成（期日：2026-11-18）
        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => now()->addDays(3)->toDateString(),
            'status' => 'completed',
        ]);

        // バッチを実行
        Artisan::call('reading-plans:daily-batch');

        // 通知が送信されていないことを確認
        Notification::assertNothingSent();
    }
}
