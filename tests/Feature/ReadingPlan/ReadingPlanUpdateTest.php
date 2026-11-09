<?php

namespace Tests\Feature\ReadingPlan;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Enums\ReadingPlanStatus;

class ReadingPlanUpdateTest extends TestCase
{
    use RefreshDatabase;

    // 正常系
    public function test_読書計画の期日を正常に変更できる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 読書計画を作成
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-11-15',
            'status' => ReadingPlanStatus::InProgress,
        ]);

        // 変更後の期日
        $newTargetDate = '2026-11-30';

        // 読書計画の期日を変更
        $response = $this->actingAs($user)->put(
            route('reading-plans.update', $readingPlan),
            [
                'target_date' => $newTargetDate,
            ]
        );

        // 読書計画一覧へリダイレクトされること
        $response->assertRedirect(route('reading-plans.index'));

        // データベースの期日が変更されていること
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'target_date' => $newTargetDate,
        ]);
    }

    public function test_期日を変更してもステータスが進行中のまま維持される(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 読書計画を作成
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-11-15',
            'status' => ReadingPlanStatus::InProgress,
        ]);

        // 変更後の期日
        $newTargetDate = '2026-11-30';

        // 読書計画の期日を変更
        $response = $this->actingAs($user)->put(
            route('reading-plans.update', $readingPlan),
            [
                'target_date' => $newTargetDate,
            ]
        );

        // 読書計画一覧へリダイレクトされること
        $response->assertRedirect(route('reading-plans.index'));

        // 期日が変更され、ステータスが進行中のまま維持されていること
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'target_date' => $newTargetDate,
            'status' => ReadingPlanStatus::InProgress->value,
        ]);
    }

    public function test_期日超過の読書計画の期日を変更するとステータスが進行中に戻る(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 読書計画を作成
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-11-01',
            'status' => ReadingPlanStatus::Overdue,
        ]);

        // 変更後の期日
        $newTargetDate = '2026-11-30';

        // 読書計画の期日を変更
        $response = $this->actingAs($user)->put(
            route('reading-plans.update', $readingPlan),
            [
                'target_date' => $newTargetDate,
            ]
        );

        // 読書計画一覧へリダイレクトされること
        $response->assertRedirect(route('reading-plans.index'));

        // 期日が変更され、ステータスが進行中に変更されていること
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'target_date' => $newTargetDate,
            'status' => ReadingPlanStatus::InProgress->value,
        ]);
    }

    public function test_今日の日付を指定した場合は正常に変更できる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 読書計画を作成
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-11-15',
            'status' => ReadingPlanStatus::InProgress,
        ]);

        // 今日の日付を指定
        $today = now()->toDateString();

        // 読書計画の期日を変更
        $response = $this->actingAs($user)->put(
            route('reading-plans.update', $readingPlan),
            [
                'target_date' => $today,
            ]
        );

        // 読書計画一覧へリダイレクトされること
        $response->assertRedirect(route('reading-plans.index'));

        // 期日が変更されていること
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'target_date' => $today,
            'status' => ReadingPlanStatus::InProgress->value,
        ]);
    }

    // 異常系
    public function test_未ログインの場合は読書計画を編集できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 読書計画を作成
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-11-15',
            'status' => ReadingPlanStatus::InProgress,
        ]);

        // 変更後の期日
        $newTargetDate = '2026-11-30';

        // 読書計画の期日を変更
        $response = $this->put(
            route('reading-plans.update', $readingPlan),
            [
                'target_date' => $newTargetDate,
            ]
        );

        // ログイン画面にリダイレクトされること
        $response->assertRedirect(route('login'));

        // 期日が変更されていないこと
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'target_date' => '2026-11-15',
            'status' => ReadingPlanStatus::InProgress->value,
        ]);
    }

    public function test_他ユーザーの読書計画を編集できない(): void
    {
        // 二名分のユーザーをそれぞれ作成
        $user = User::factory()->create(); // ログインするユーザー
        $otherUser = User::factory()->create(); // 他ユーザー

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        // 他のユーザー：読書計画を作成
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
            'target_date' => '2026-11-15',
            'status' => ReadingPlanStatus::InProgress,
        ]);

        // 変更後の期日
        $newTargetDate = '2026-11-30';

        // ログインユーザー：読書計画の期日を変更
        $response = $this->actingAs($user)->put(
            route('reading-plans.update', $readingPlan),
            [
                'target_date' => $newTargetDate,
            ]
        );

        // 403 Forbidden が返されることを確認
        $response->assertForbidden();

        // 期日が変更されていないこと
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'target_date' => '2026-11-15',
            'status' => ReadingPlanStatus::InProgress->value,
        ]);
    }

    public function test_期日が未入力の場合はバリデーションエラーになる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 読書計画を作成
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-11-15',
            'status' => ReadingPlanStatus::InProgress,
        ]);

        // 読書計画の期日を変更（未入力）
        $response = $this->actingAs($user)->put(
            route('reading-plans.update', $readingPlan),
            [
                'target_date' => '',
            ]
        );

        // バリデーションエラー確認
        $response->assertSessionHasErrors('target_date');

        // 期日が変更されていないこと
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'target_date' => '2026-11-15',
            'status' => ReadingPlanStatus::InProgress->value,
        ]);
    }

    public function test_期日に過去の日付を指定した場合はバリデーションエラーになる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 読書計画を作成
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-11-15',
            'status' => ReadingPlanStatus::InProgress,
        ]);

        // 昨日の日付を指定
        $pastDate = now()->subDay()->toDateString();

        // 読書計画の期日を変更
        $response = $this->actingAs($user)->put(
            route('reading-plans.update', $readingPlan),
            [
                'target_date' => $pastDate,
            ]
        );

        // バリデーションエラーを確認
        $response->assertSessionHasErrors('target_date');

        // 期日が変更されていないこと
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'target_date' => '2026-11-15',
            'status' => ReadingPlanStatus::InProgress->value,
        ]);
    }

    public function test_存在しない読書計画を編集できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 存在しない読書計画IDを指定して期日を変更
        $response = $this->actingAs($user)->put(
            route('reading-plans.update', 9999),
            [
                'target_date' => '2026-11-30',
            ]
        );

        // 404 Not Found が返されること
        $response->assertNotFound();
    }
}
