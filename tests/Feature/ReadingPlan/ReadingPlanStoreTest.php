<?php

namespace Tests\Feature\ReadingPlan;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;

class ReadingPlanStoreTest extends TestCase
{
    use RefreshDatabase;

    // 正常系
    public function test_読書計画を正常に登録できる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create();

        // 読書計画の登録データ
        $data = [
            'book_id' => $book->id,
            'target_date' => now()->addDays(7)->format('Y-m-d'),
        ];

        // ログインして読書計画を登録
        $response = $this->actingAs($user)
            ->post(route('reading-plans.store'), $data);

        // 登録後、読書計画一覧画面にリダイレクトされること
        $response->assertRedirect(route('reading-plans.index'));

        // データベースに読書計画が登録されていること
        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $data['target_date'],
        ]);
    }

    public function test_登録した読書計画が進行中のステータスになる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create();

        // 読書計画の登録データ
        $data = [
            'book_id' => $book->id,
            'target_date' => now()->addDays(7)->format('Y-m-d'),
        ];

        // ログインして読書計画を登録
        $this->actingAs($user)
            ->post(route('reading-plans.store'), $data);

        // 登録した読書計画が進行中のステータスであること
        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_登録した読書計画の完了日時がnullになる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create();

        // 読書計画の登録データ
        $data = [
            'book_id' => $book->id,
            'target_date' => now()->addDays(7)->format('Y-m-d'),
        ];

        // ログインして読書計画を登録
        $this->actingAs($user)
            ->post(route('reading-plans.store'), $data);

        // 登録した読書計画の完了日時がnullであること
        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'completed_at' => null,
        ]);
    }

    // 異常系
    public function test_未ログインの場合は読書計画を登録できない(): void
    {
        // 書籍を作成
        $book = Book::factory()->create();

        // 読書計画の登録データ
        $data = [
            'book_id' => $book->id,
            'target_date' => now()->addDays(7)->format('Y-m-d'),
        ];

        // 未ログイン状態で読書計画を登録
        $response = $this->post(route('reading-plans.store'), $data);

        // ログイン画面にリダイレクトされること
        $response->assertRedirect(route('login'));

        // reading_plansテーブルに登録されていないこと
        $this->assertDatabaseMissing('reading_plans', [
            'book_id' => $book->id,
        ]);
    }

    public function test_存在しない書籍を指定した場合は登録できない(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 読書計画の登録データ
        $data = [
            'book_id' => 9999,
            'target_date' => now()->addDays(7)->format('Y-m-d'),
        ];

        // ログインして読書計画を登録
        $response = $this->actingAs($user)
            ->post(route('reading-plans.store'), $data);

        // バリデーションエラー確認
        $response->assertSessionHasErrors('book_id');

        // reading_plansテーブルに登録されていないこと
        $this->assertDatabaseCount('reading_plans', 0);
    }

    public function test_期日が未入力の場合はバリデーションエラーになる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 書籍を作成
        $book = Book::factory()->create();

        // 読書計画の登録データ
        $data = [
            'book_id' => $book->id,
            'target_date' => '',
        ];

        // ログインして読書計画を登録
        $response = $this->actingAs($user)
            ->post(route('reading-plans.store'), $data);

        // バリデーションエラー確認
        $response->assertSessionHasErrors('target_date');

        // reading_plansテーブルに登録されていないこと
        $this->assertDatabaseCount('reading_plans', 0);
    }
}
