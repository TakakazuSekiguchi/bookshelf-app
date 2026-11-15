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

class NotificationIndexTest extends TestCase
{
    use RefreshDatabase;

    // 毎回日時を解除
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_ログインユーザーの通知一覧を表示できる(): void
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
        ]);

        // バッチを実行
        Artisan::call('reading-plans:daily-batch');

        // ログインユーザーとして通知一覧画面にアクセス
        $response = $this->actingAs($user)->get(route('notifications.index'));

        // 通知一覧にログインできている
        $response->assertOk();
        $response->assertSee('通知一覧');

        // 「期日が3日後の読書計画」が表示されている
        $response->assertSee('読書計画リマインド — 期限まであと 3 日');
    }

    public function test_他のユーザーの通知は表示されない(): void
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
        ]);

        // バッチを実行
        Artisan::call('reading-plans:daily-batch');

        // ログインユーザーとして通知一覧画面にアクセス
        $response = $this->actingAs($user)->get(route('notifications.index'));

        // 通知一覧にログインできている
        $response->assertOk();
        $response->assertSee('通知一覧');

        // 他のユーザー宛ての通知が表示されない
        $response->assertDontSee('読書計画リマインド — 期限まであと 3 日');
    }

    public function test_未認証ユーザーは通知一覧を取得できない(): void
    {
        // 未認証ユーザーとして通知一覧画面にアクセス
        $response = $this->get(route('notifications.index'));

        // ログイン画面にリダイレクトされる
        $response->assertRedirect(route('login'));
    }
}
