<?php

use App\Http\Controllers\Api\BookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Sanctum認証の確認や、認証済みユーザー情報の取得に利用
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