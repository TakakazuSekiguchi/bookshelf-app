<?php

namespace Tests\Feature\ReadingPlan;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Enums\ReadingPlanStatus;

class ReadingPlanDestroyTest extends TestCase
{
    use RefreshDatabase;

    // 正常系
    public function test_読書計画を正常に削除できる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 読書計画を作成
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        // ログインして読書計画を削除
        $response = $this->actingAs($user)
            ->delete(route('reading-plans.destroy', $readingPlan));

        // 読書計画一覧画面にリダイレクトされること
        $response->assertRedirect(route('reading-plans.index'));

        // DBから読書計画が削除されていること
        $this->assertDatabaseMissing('reading_plans', [
            'id' => $readingPlan->id,
        ]);
    }

    // 異常系
    public function test_未ログインの場合は読書計画を削除できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 読書計画を作成
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        // 未ログイン状態で読書計画を削除
        $response = $this->delete(route('reading-plans.destroy', $readingPlan));

        // ログイン画面にリダイレクトされること
        $response->assertRedirect(route('login'));

        // 読書計画が削除されていないこと
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
        ]);
    }

    public function test_他ユーザーの読書計画を削除できない(): void
    {
        // 二名分のユーザーをそれぞれ作成
        $user = User::factory()->create(); // ログインするユーザー
        $otherUser = User::factory()->create(); // 他ユーザー

        // 他のユーザー：読書計画を作成
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        // ログインして読書計画を削除
        $response = $this->actingAs($user)
            ->delete(route('reading-plans.destroy', $readingPlan));

        // 403 Forbidden が返されることを確認
        $response->assertForbidden();

        // 読書計画が削除されていないこと
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
        ]);
    }

    public function test_存在しない読書計画を削除できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 存在しない読書計画IDを指定して削除
        $response = $this->actingAs($user)
            ->delete(route('reading-plans.destroy', 9999));

        // 404 Not Found が返されること
        $response->assertNotFound();
    }
}
