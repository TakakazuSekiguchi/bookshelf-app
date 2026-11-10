<?php

namespace Tests\Feature\ReadingPlan;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Enums\ReadingPlanStatus;

class ReadingPlanCompleteTest extends TestCase
{
    use RefreshDatabase;

    // 正常系
    public function test_読書計画を正常に完了できる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 読書計画を作成
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::InProgress,
            'completed_at' => null,
        ]);

        // 読書計画の完了処理を実行
        $response = $this->actingAs($user)
            ->post(route('reading-plans.complete', $readingPlan));

        // 読書計画一覧画面にリダイレクトされること
        $response->assertRedirect(route('reading-plans.index'));

        // 読書計画のステータスが完了になっていること
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => ReadingPlanStatus::Completed->value,
        ]);

        // completed_at に日時が保存されていること
        $this->assertNotNull(
            $readingPlan->fresh()->completed_at
        );
    }

    // 異常系
    public function test_未ログインの場合は読書計画を完了できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 読書計画を作成
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::InProgress,
            'completed_at' => null,
        ]);

        // 未ログイン状態で読書計画の完了処理を実行
        $response = $this->post(route('reading-plans.complete', $readingPlan));

        // ログイン画面にリダイレクトされること
        $response->assertRedirect(route('login'));

        // ステータスが変更されていないこと
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => ReadingPlanStatus::InProgress->value,
            'completed_at' => null,
        ]);
    }

    public function test_他ユーザーの読書計画を完了できない(): void
    {
        // 二名分のユーザーをそれぞれ作成
        $user = User::factory()->create(); // ログインするユーザー
        $otherUser = User::factory()->create(); // 他ユーザー

        // 他のユーザー：読書計画を作成
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
            'status' => ReadingPlanStatus::InProgress,
            'completed_at' => null,
        ]);

        // ログインユーザー：読書計画の完了処理を実行
        $response = $this->actingAs($user)
            ->post(route('reading-plans.complete', $readingPlan));

        // 403 Forbidden が返されることを確認
        $response->assertForbidden();

        // ステータスが変更されていないこと
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => ReadingPlanStatus::InProgress->value,
            'completed_at' => null,
        ]);
    }

    public function test_存在しない読書計画を完了できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 存在しない読書計画IDを指定して読書計画の完了処理を実行
        $response = $this->actingAs($user)
            ->post(route('reading-plans.complete', 9999));

        // 404 Not Found が返されること
        $response->assertNotFound();
    }
}
