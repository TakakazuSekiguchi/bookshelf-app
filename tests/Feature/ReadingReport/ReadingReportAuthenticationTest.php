<?php

namespace Tests\Feature\ReadingReport;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
// use App\Models\Book;
// use App\Models\Genre;
// use App\Models\Review;
// use App\Models\ReadingPlan;
use App\Models\User;
// use App\Enums\ReadingPlanStatus;

class ReadingReportAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    // 認証・認可
    public function test_未認証ユーザーは読書レポートにアクセスできない(): void
    {
        // 読書レポートにアクセス
        $response = $this->get(route('reports.index'));

        // ログイン画面にリダイレクトされることを確認
        $response->assertRedirect(route('login'));
    }

    public function test_認証済みユーザーは読書レポートを表示できる(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 認証済みユーザーとして読書レポートにアクセス
        $response = $this->actingAs($user)->get(route('reports.index'));

        // 正常に表示されることを確認
        $response->assertOk();

        // 読書レポートのビューが表示されることを確認
        $response->assertViewIs('reports.index');

        // 統計情報が渡されていることを確認
        $response->assertViewHas('stats');
    }
}
