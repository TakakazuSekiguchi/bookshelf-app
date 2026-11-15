<?php

namespace Tests\Feature\Notifications;

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

class NotificationReadTest extends TestCase
{
    use RefreshDatabase;

    // 毎回日時を解除
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_ログインユーザーは通知を既読にできる(): void
    {
        // 本日の日付を固定
        Carbon::setTestNow(Carbon::parse('2026-11-15'));

        // ユーザーを作成
        $user = User::factory()->create();

        // 期日が3日後の読書計画を作成（期日：2026-11-18）
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => now()->addDays(3)->toDateString(),
            'status' => 'in_progress',
            'completed_at' => null,
        ]);

        // バッチを実行
        Artisan::call('reading-plans:daily-batch');

        // 作成された通知を取得
        $notification = $user->notifications()->first();

        // ログインユーザーとして通知の既読処理
        $response = $this->actingAs($user)->post(route('notifications.read', $notification));

        // 通知が既読になっていることを確認
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_他のユーザーの通知を既読にできない(): void
    {
        // 本日の日付を固定
        Carbon::setTestNow(Carbon::parse('2026-11-15'));

        // 二名分のユーザーをそれぞれ作成
        $user = User::factory()->create(); // ログインするユーザー
        $otherUser = User::factory()->create(); // 他ユーザー

        // 他のユーザーに通知を作成
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
            'target_date' => now()->addDays(3)->toDateString(),
            'status' => 'in_progress',
            'completed_at' => null,
        ]);

        // バッチを実行
        Artisan::call('reading-plans:daily-batch');

        // 作成された通知を取得
        $notification = $otherUser->notifications()->first();

        // ログインユーザーとして通知の既読処理
        $response = $this->actingAs($user)->post(route('notifications.read', $notification));

        // 404 Not Found が返されることを確認
        $response->assertNotFound();

        // 他のユーザーの通知が未読のままであることを確認
        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_未認証ユーザーは通知を既読にできない(): void
    {
        // 本日の日付を固定
        Carbon::setTestNow(Carbon::parse('2026-11-15'));

        // ユーザーを作成
        $user = User::factory()->create();

        // 読書計画を作成
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => now()->addDays(3)->toDateString(),
            'status' => 'in_progress',
            'completed_at' => null,
        ]);

        // バッチを実行
        Artisan::call('reading-plans:daily-batch');

        // 作成された通知を取得
        $notification = $user->notifications()->first();

        // 未認証ユーザーとして通知の既読処理
        $response = $this->post(route('notifications.read', $notification->id));

        // ログイン画面にリダイレクトされることを確認
        $response->assertRedirect(route('login'));

        // 通知が未読のままであることを確認
        $this->assertNull($notification->fresh()->read_at);
    }
}
