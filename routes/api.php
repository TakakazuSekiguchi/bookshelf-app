<?php

use App\Http\Controllers\Api\BookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Sanctum認証が必要なルート
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('/v1/books', BookController::class)
        ->only(['store', 'update', 'destroy']);
});

// 公開API（認証不要）
Route::apiResource('/v1/books', BookController::class)->only(['index', 'show']);

/*memo
・書き込み系APIのルーティングを設定する
store・update・delete に auth:sanctum を適用します。

・コントローラーの処理を実装する
登録・更新・削除に加えて、認証ユーザーと書籍所有者の確認を行います。

・Postmanで動作確認する
正常系、未認証、他ユーザーの書籍に対する更新・削除などを確認します。
*/